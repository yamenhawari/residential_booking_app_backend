<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\AppNotification;
use App\Services\FCMService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function getStats()
    {
        return response()->json([
            'total_users' => User::count(),
            'pending_users' => User::where('status', 'inactive')->orWhere('status', 'pending')->count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_apartments' => Apartment::count(),
            'total_bookings' => Booking::count(),
        ]);
    }

    public function getUsers(Request $request)
    {
        $query = User::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'active';
        $user->save();

        return response()->json(['message' => 'User approved successfully']);
    }

    public function rejectUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'blocked';
        $user->save();

        return response()->json(['message' => 'User rejected']);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function broadcastNotification(Request $request, FCMService $fcm)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $users = User::whereNotNull('fcm_token')->get();
        $count = 0;

        foreach ($users as $user) {
            AppNotification::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'body' => $request->body,
                'type' => 'info',
                'is_read' => false
            ]);

            try {
                $fcm->send($user->fcm_token, $request->title, $request->body);
                $count++;
            } catch (\Exception $e) {
                // Continue if one fails
            }
        }

        return response()->json(['message' => "Sent to $count users"]);
    }
}
