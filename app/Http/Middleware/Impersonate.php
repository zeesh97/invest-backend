<?php

namespace App\Http\Middleware;

use App\Models\Impersonation;
use App\Models\User;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Log;
use Symfony\Component\HttpFoundation\Response;

class Impersonate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if ($token) {
            $impersonation = Impersonation::where('token', $token)
                ->whereNull('ended_at')
                ->where('expires_at', '>', now())
                ->first();

            if ($impersonation) {
                $impersonatedUser = User::find($impersonation->impersonated_id);

                if ($impersonatedUser) {
                    Auth::setUser($impersonatedUser);
                    $impersonatedUser->impersonated_user = true;
                    $request->merge(['impersonated_user' => true]);
                }
            }
        }

        return $next($request);
    }
}
