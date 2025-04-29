<?php

namespace App\Http\Middleware;

use App\Http\Helpers\Helper;
use App\Models\Subscription;
use Closure;
use DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLoggedInUserLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $activeSessions = DB::table('sessions')->count();
        $subscriptions = Subscription::whereNull('expired_at')
            ->whereColumn('usage_login_users', '<', 'login_users')
            ->get();

        if ($subscriptions->isEmpty()) {
            return Helper::sendError('The limit exceeded or the subscription has expired.', [], 403);
        }

        if ($activeSessions >= $subscriptions->sum('login_users')) {
            return  Helper::sendError(
                'Login user limit reached. Try again later.',
                [],
                429
            );
        }
        return $next($request);
    }
}
