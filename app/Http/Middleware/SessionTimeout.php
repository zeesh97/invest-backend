<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity');
            $timeout = config('session.lifetime') * 60; // Convert minutes to seconds

            if ($lastActivity && (time() - $lastActivity > $timeout)) {
                // Logout user if inactive for too long
                Auth::logout();
                session()->invalidate();
                return response()->json(['error' => 'Session expired due to inactivity'], 401);
            }

            session(['last_activity' => time()]);
        }
        return $next($request);
    }
}
