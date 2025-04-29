<?php

namespace App\Http\Middleware;

use App\Http\Helpers\Helper;
use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTotalUserLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subscriptions = Subscription::whereNull('expired_at')
            ->whereColumn('usage_total_users', '<', 'total_users')
            ->get();

        if ($subscriptions->isEmpty()) {
            return Helper::sendError('The total users limit has been exceeded or the subscription has expired.', [], 403);
        }
        return $next($request);
    }
}
