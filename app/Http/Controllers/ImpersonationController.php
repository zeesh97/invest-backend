<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoginUserResource;
use App\Models\Impersonation;
use App\Models\User;
use Auth;
use DB;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function impersonate(Request $request, $userId)
    {
        $admin = Auth::user()->hasRole('admin') ? Auth::user() : null;
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized - Admin role required'], 403);
        }
        $token = $request->bearerToken();

        if ($admin->id === (int)$userId) {
            return response()->json(['error' => 'Cannot impersonate yourself.'], 403);
        }

        $existing = Impersonation::where('admin_id', $admin->id)
            ->whereNull('ended_at')
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Already impersonating another user'], 400);
        }

        if (Impersonation::where('impersonated_id', $userId)->whereNull('ended_at')->exists()) {
            return response()->json(['error' => 'This user is already being impersonated'], 400);
        }

        try {
            DB::beginTransaction();

            $impersonatedUser = User::findOrFail($userId);

            Impersonation::create([
                'admin_id'        => $admin->id,
                'impersonated_id' => $userId,
                'token'           => $token,
                'ip_address'      => $request->ip(),
                'user_agent'      => $request->userAgent(),
                'expires_at'      => now()->addMinutes(30),
            ]);

            DB::commit();

            // Middleware will auto-set user on future requests
            $impersonatedUser->impersonated_user = true;
            $impersonatedUser->token = $token;
            return response()->json([
                'token' => $token,
                'impersonated_user' => new LoginUserResource($impersonatedUser),
                'expires_at' => now()->addMinutes(30),
                'message' => 'Impersonation started successfully for 30 minutes',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['error' => 'Failed to impersonate: ' . $e->getMessage()], 500);
        }
    }

    public function stopImpersonating(Request $request)
    {
        $currentUser = Auth::user();
        try {
            DB::beginTransaction();

            $token = $request->bearerToken();

            $impersonation = Impersonation::where('token', $token)
                ->whereNull('ended_at')
                ->first();

            if (!$impersonation && !$currentUser->hasRole('admin')) {
                $currentUser->currentAccessToken()->delete();
                DB::commit();
                return response()->json(['error' => 'No active impersonation found'], 404);
            }
            if (!$impersonation) {
                DB::commit();
                return response()->json(['error' => 'No active impersonation found'], 404);
            }

            if ($impersonation->expires_at <= now()) {
                $impersonation->update(['ended_at' => now()]);
                $currentUser->currentAccessToken()->delete();
                DB::commit();
                return response()->json(['error' => 'Impersonation session expired'], 401);
            }

            // End impersonation
            $impersonation->update(['ended_at' => now()]);

            // Optionally: Delete record instead of soft-end
            $impersonation->delete();

            $originalUser = User::find($impersonation->admin_id);
            if (!$originalUser) {
                DB::rollBack();
                return response()->json(['error' => 'Original user not found'], 404);
            }

            DB::commit();
            $originalUser->token = $token;
            return response()->json([
                'message' => 'Impersonation stopped.',
                'current_user' => new LoginUserResource($originalUser),
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'error' => 'Failed to stop impersonation.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
