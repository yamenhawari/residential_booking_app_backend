<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckLoginStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::where('phone', $request->phone)->first();
        if ($user && $user->status !== 'active') {
            return response()->json([
                'message' => 'Account not active, waiting for admin approval'
            ], 403);
        }

        return $next($request);
    }
}
