<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class APIGuestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('Authorization') && $request->header('Authorization') !== 'Bearer null' && app('auth')->guard('api')) {
            $request->merge(['user' => auth('api')->user()]);
            return $next($request);
        }

        // Allow request to proceed even if no auth (Controller will check $request->user())
        return $next($request);
    }
}
