<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle($request, Closure $next)
{
    if (auth()->check() && auth()->user()->role === 'tenant') {
        return $next($request);
    }

    return response()->json(['message' => 'You do not have permission (tenants only)'], 403);
}

}
