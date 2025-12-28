<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CanBook
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // ❌ الأدمن ممنوع يحجز
        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrators are not allowed to make bookings.'
            ], 403);
        }

        // ✅ يسمح للمالك والمستأجر
        if (!in_array($user->role, ['owner', 'tenant'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to make a booking.'
            ], 403);
        }

        return $next($request);
    }
}
