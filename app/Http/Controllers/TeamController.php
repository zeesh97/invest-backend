<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Services\TeamService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TeamController extends Controller
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

            if (!Auth::user()->hasRole('admin')) {
                return Helper::sendError('You are not authorized.', [], Response::HTTP_UNAUTHORIZED);
            }
            // return TeamResource::collection(Team::with('form')->latest()->paginate($perPage));
            return TeamResource::collection(Team::with('forms', 'locations', 'managers')->latest()->paginate($perPage));
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    public function getAllTeams(): JsonResponse
    {
        return Helper::sendResponse(Team::select(['id', 'name'])->get(), 'Success', 200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request): JsonResponse
    {
        try {
            $data = [];
            $team = '';
            DB::transaction(function () use ($request, $data, $team) {

                $team = Team::create([
                    'name' => $request['name'],
                ]);
                foreach ($request['location_ids'] as $key => $location_id) {
                    $data[] = [
                        'form_id' => $request['form_ids'][$key],
                        'location_id' => $location_id,
                        'manager_id' => $request['manager_ids'][$key],
                        'team_id' => $team->id,
                    ];
                }
                $team->managers()->attach($data);
            });
            return Helper::sendResponse($team, 'Successfully added', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return Helper::sendError($e->getMessage(), [], 403);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        try {
            $data = [];
            DB::transaction(function () use ($request, $data, $team) {

                $team->update([
                    'name' => $request['name'],
                ]);
                foreach ($request['location_ids'] as $key => $location_id) {
                    $data[] = [
                        'form_id' => $request['form_ids'][$key],
                        'location_id' => $location_id,
                        'manager_id' => $request['manager_ids'][$key],
                        'team_id' => $team->id,
                    ];
                }
                DB::table('form_location_manager_team')->where('team_id', $team->id)->delete();
                DB::table('form_location_manager_team')->where('team_id', $team->id)->insert($data);
                // DB::table('form_location_manager_team')->where('team_id', $team->id)->update($data);

            //     DB::table('form_location_manager_team')
            //         ->where('team_id', $team->id)
            //         ->update($data);
            });
            return Helper::sendResponse(new TeamResource($team), 'Successfully updated', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return Helper::sendError($e->getMessage(), [], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(Team $team): Response
    // {
    //     //
    // }

    public function teamByForm(Request $request, TeamService $teamService)
    {
        $validated = $request->validate(['form_id' => 'required|exists:forms,id']);
        $response = $teamService->teamByFormId($validated['form_id']);
        return Helper::sendResponse($response, 'Success', 200);
    }
    public function teamMembersById(Request $request, TeamService $teamService)
    {
        $validated = $request->validate(['team_id' => 'required|exists:teams,id']);
        $response = $teamService->teamMembersById($validated['team_id']);
        return Helper::sendResponse($response, 'Success', 200);
    }
}
