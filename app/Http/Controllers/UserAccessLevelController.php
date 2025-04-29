<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Models\UserAccessLevel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserAccessLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $userId = $request->input('user_id');
        $accessibleType = $request->input('accessible_type');
        $accessibleId = $request->input('accessible_id');

        $accessLevels = UserAccessLevel::when($userId, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($accessibleType, function ($query, $accessibleType) {
                $query->where('accessible_type', $accessibleType);
            })
            ->when($accessibleId, function ($query, $accessibleId) {
                $query->where('accessible_id', $accessibleId);
            })
            ->with('user', 'accessible') // Eager load relationships
            ->paginate($perPage);
            return Helper::sendResponse($accessLevels, 'Success', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request): Response
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    // public function show(UserAccessLevel $userAccessLevel): Response
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, UserAccessLevel $userAccessLevel): Response
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(UserAccessLevel $userAccessLevel): Response
    // {
    //     //
    // }
}
