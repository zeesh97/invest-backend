<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Requests\LoginUserRequest;
use App\Http\Resources\LoginUserResource;
use App\Http\Resources\UserResource;
use App\Models\IpRestriction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Multitenancy\Models;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{

    public function login(LoginUserRequest $request)
    {
        $clientIp = $request->ip();
        // dd($clientIp);
        $restriction = IpRestriction::where('ip_address', $clientIp)->first();

        if ($restriction && $restriction->type === 'restrict') {
            return Helper::sendError('Your IP address is restricted.', [], Response::HTTP_FORBIDDEN);
        }

        $allowRulesExist = IpRestriction::where('type', 'allow')->exists();

        if ($allowRulesExist && (!$restriction || $restriction->type !== 'allow')) {
            return Helper::sendError('Your IP address is not allowed.', [], Response::HTTP_FORBIDDEN);
        }

        $request->validated($request->only(['email', 'password']));

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return Helper::sendError('Credentials do not match', '[]', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('Token')->plainTextToken;

        $user->token = $token;
        return new LoginUserResource($user);
    }

    public function superAdminLogin(LoginUserRequest $request)
    {
        $request->validated($request->only(['email', 'password']));

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return Helper::sendError('Credentials do not match', '[]', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('Token')->plainTextToken;

        $user->token = $token;
        return new LoginUserResource($user);
    }

    // public function logout()
    // {
    //     try {
    //         $user = Auth::user();
    //         if (!$user) {
    //             return Helper::sendError('User is not authenticated', [], 401);
    //         }

    //         if ($user) {
    //             $token = $user->currentAccessToken();
    //             if ($token instanceof PersonalAccessToken) {
    //                 $token->delete();
    //             }

    //             Auth::guard('web')->logout(); // Or Auth::logout() if using the default web guard

    //             return Helper::sendResponse(null, 'Logged out successfully.');
    //         }

    //         return Helper::sendResponse(null, 'You have successfully been logged out');
    //     } catch (\Throwable $e) {

    //         return Helper::sendError($e->getMessage(), [], 500);
    //     }
    // }
    public function logout()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return Helper::sendError('User is not authenticated', [], 401);
            }

            // Revoke the current user's token (the one used for this request)
            $user->currentAccessToken()->delete();

            return Helper::sendResponse(null, 'Logged out successfully.');
        } catch (\Throwable $e) {
            return Helper::sendError($e->getMessage(), [], 500);
        }
    }
}
