<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Apartment; // Assuming you have this
use App\Models\Booking;   // Assuming you have this
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // 1. Get Dashboard Statistics
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

    // 2. Get All Users (with optional filtering)
    public function getUsers(Request $request)
    {
        $query = User::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or phone
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

    // 3. Approve User
    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'active';
        $user->save();

        return response()->json(['message' => 'User approved successfully']);
    }

    // 4. Reject User (Set to blocked or inactive)
    public function rejectUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'blocked';
        $user->save();

        return response()->json(['message' => 'User rejected']);
    }

    // 5. Delete User (Hard Delete)
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
