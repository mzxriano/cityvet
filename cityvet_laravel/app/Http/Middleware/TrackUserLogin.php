<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Update last login only if it's been more than 5 minutes
            // to avoid updating on every request
            if (!$user->last_login_at || $user->last_login_at->diffInMinutes(now()) > 5) {
                $user->updateLastLogin();
            }
        }

        return $next($request);
    }
}
