<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Requests\StoreTeamMemberRequest;
use App\Http\Requests\UpdateTeamMemberRequest;
use App\Http\Resources\TeamMemberResource;
use App\Http\Resources\UserResource;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $rules = [];

            if ($request->has('perPage')) {
                $rules['perPage'] = ['nullable', 'integer', 'min:1', 'max:100'];
            }

            $validated = $request->validate($rules);
            $perPage = $request->has('perPage') ? $validated['perPage'] : 10;

            if (!auth()->user()->hasRole('admin')) {
                return Helper::sendError('You are not authorized.', [], Response::HTTP_UNAUTHORIZED);
            }
            return TeamMemberResource::collection(Team::with('members')->latest()->paginate($perPage));

        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamMemberRequest $request): JsonResponse
    {
        // dd($request->member_ids);
        try {
            $result = \DB::transaction(function () use ($request) {

                $team = Team::find($request->team_id);
                $memberIds = $request->member_ids;
                $managerIds = $team->managers()->pluck('manager_id')->toArray();
                $mergedIds = array_unique(array_merge($memberIds, $managerIds));
                $team->members()->attach($mergedIds);
                return $request->member_ids;
            });
            return Helper::sendResponse($result, 'Successfully added', 201);
        } catch (\Exception $e) {
            \DB::rollback();
            return Helper::sendError($e->getMessage(), [], 403);
        }
    }

    public function update(UpdateTeamMemberRequest $request, int $id): JsonResponse
    {
        try {
            \DB::beginTransaction();
            $team = Team::findOrFail($id);
            $memberIds = $request->member_ids;
            $managerIds = $team->managers()->pluck('manager_id')->toArray();
            $mergedIds = array_unique(array_merge($memberIds, $managerIds));

            $result = $team->members()->sync($mergedIds);

            \DB::commit();

            return Helper::sendResponse($result, 'Successfully added', 201);
        } catch (\Exception $e) {
            \DB::rollback();
            return Helper::sendError($e->getMessage(), [], 403);
        }
    }
    // public function destroy(TeamMember $teamMember): Response
    // {
    //     //
    // }
}
