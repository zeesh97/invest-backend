<?php

namespace App\Http\Middleware;

use App\Http\Helpers\Helper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Subscription;
use DB;

class CheckStorageLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subscriptions = Subscription::whereNull('expired_at')
            ->whereColumn('usage_data_mb', '<', 'data_mb')
            ->get();

        if ($subscriptions->isEmpty()) {
            return Helper::sendError('The storage limit has been exceeded or the subscription has expired.', [], 403);
        }

        return $next($request);
    }
}
