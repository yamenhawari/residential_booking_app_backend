<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected $fcm;

    public function __construct(FCMService $fcm)
    {
        $this->fcm = $fcm;
    }

    public function index()
    {
        $userId = Auth::id();

        $conversations = Conversation::where('user1_id', $userId)
            ->orWhere('user2_id', $userId)
            ->with(['user1:id,first_name,last_name,profile_image', 'user2:id,first_name,last_name,profile_image', 'latestMessage'])
            ->get()
            ->map(function ($c) use ($userId) {
                $otherUser = $c->user1_id == $userId ? $c->user2 : $c->user1;
                $lastMsg = $c->latestMessage;

                return [
                    'id' => $c->id,
                    'other_user_id' => $otherUser->id,
                    'other_user_name' => $otherUser->first_name . ' ' . $otherUser->last_name,
                    'other_user_image' => $otherUser->profile_image,
                    'last_message' => $lastMsg ? $lastMsg->body : 'No messages yet',
                    'last_message_time' => $lastMsg ? $lastMsg->created_at : $c->created_at,
                    'unread_count' => ($lastMsg && $lastMsg->sender_id !== $userId && !$lastMsg->is_read) ? 1 : 0
                ];
            })
            ->sortByDesc('last_message_time')
            ->values();

        return response()->json(['success' => true, 'data' => $conversations]);
    }

    public function startChat(Request $request)
    {
        $request->validate(['receiver_id' => 'required|exists:users,id|integer']);

        $currentId = Auth::id();
        $receiverId = $request->receiver_id;

        if ($currentId == $receiverId) {
            return response()->json(['message' => 'Cannot chat with yourself'], 400);
        }

        // Ensure strictly ordered IDs to prevent duplicate conversations (1-2 and 2-1)
        $u1 = min($currentId, $receiverId);
        $u2 = max($currentId, $receiverId);

        $conversation = Conversation::firstOrCreate([
            'user1_id' => $u1,
            'user2_id' => $u2
        ]);

        return response()->json(['success' => true, 'data' => ['id' => $conversation->id]]);
    }

    public function getMessages($id)
    {
        $userId = Auth::id();

        $conversation = Conversation::where('id', $id)
            ->where(function ($q) use ($userId) {
                $q->where('user1_id', $userId)
                    ->orWhere('user2_id', $userId);
            })
            ->first();

        if (!$conversation) {
            return response()->json(['message' => 'Conversation not found or access denied'], 403);
        }

        Message::where('conversation_id', $id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where('conversation_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $messages]);
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate(['body' => 'required|string|max:5000']);
        $userId = Auth::id();

        $conversation = Conversation::where('id', $id)
            ->where(function ($q) use ($userId) {
                $q->where('user1_id', $userId)
                    ->orWhere('user2_id', $userId);
            })
            ->firstOrFail();

        $message = Message::create([
            'conversation_id' => $id,
            'sender_id' => $userId,
            'body' => $request->body
        ]);

        $receiverId = ($conversation->user1_id === $userId) ? $conversation->user2_id : $conversation->user1_id;

        $receiver = User::select('id', 'fcm_token')->find($receiverId);

        if ($receiver && $receiver->fcm_token) {
            try {
                $senderName = Auth::user()->first_name;
                $this->fcm->send(
                    $receiver->fcm_token,
                    "Message from $senderName",
                    $request->body
                );
            } catch (\Exception $e) {
            }
        }

        return response()->json(['success' => true, 'data' => $message]);
    }

    public function deleteConversation($id)
    {
        $conversation = Conversation::where('id', $id)
            ->where(function ($q) {
                $q->where('user1_id', Auth::id())
                    ->orWhere('user2_id', Auth::id());
            })
            ->firstOrFail();

        $conversation->delete();
        return response()->json(['success' => true, 'message' => 'Conversation deleted']);
    }

    public function deleteMessage($id)
    {
        $message = Message::where('id', $id)
            ->where('sender_id', Auth::id())
            ->firstOrFail();

        $message->delete();
        return response()->json(['success' => true, 'message' => 'Message deleted']);
    }
}
