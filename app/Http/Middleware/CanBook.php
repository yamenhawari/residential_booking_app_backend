<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CanBook
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrators are not allowed to make bookings.'
            ], 403);
        }

        if (!in_array($user->role, ['owner', 'tenant'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to make a booking.'
            ], 403);
        }

        return $next($request);
    }
}
