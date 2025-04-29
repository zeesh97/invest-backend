<?php

namespace App\Services;

// use App\Http\Resources\LoginUserResource;

use App\Http\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ServiceTeam
{
    public function index(Request $request): JsonResponse
    {
        try {
            if ($request->has('all')) {
                $teams = Service::with(['teams:id,name'])->select(['id', 'name'])->latest()->get();
                return Helper::sendResponse($teams, 'Success');
            } else {
                $perPage = $request->get('per_page', default: 10);
                $teams = Service::with(['teams:id,name'])
                    ->select(['id', 'name'])
                    ->latest()
                    ->paginate($perPage);

                return Helper::sendResponse($teams, 'Success', 200);
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }
    public function show(int $id): JsonResponse
    {
        try {
            $service = Service::with(['teams:id,name'])
                ->select(['id', 'name'])
                ->find($id);

            return Helper::sendResponse($service, 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }
    public function attachTeams(Request $request, Service $service)
    {
        $request->validate([
            'team_ids' => 'required|array',
        ]);

        $teamIds = $request->input('team_ids');

        $existingTeamIds = DB::table('teams')
            ->whereIn('id', $teamIds)
            ->pluck('id')
            ->toArray();

        $invalidIds = array_diff($teamIds, $existingTeamIds);

        if (!empty($invalidIds)) {
            return Helper::sendError('Invalid team IDs: ',  implode(', ', $invalidIds), 422);
        }

        $service->teams()->sync($existingTeamIds);
        return $service->load('teams');
    }

    public function detachTeams(Request $request, Service $service)
    {
        $request->validate([
            'team_ids' => 'required|array',
        ]);

        $teamIds = $request->input('team_ids');

        $existingAttachedTeamIds = DB::table('service_team')
            ->where('service_id', $service->id)
            ->whereIn('team_id', $teamIds)
            ->pluck('team_id')
            ->toArray();


        $invalidIds = array_diff($teamIds, $existingAttachedTeamIds);

        if (!empty($invalidIds)) {
            return response()->json(['error' => 'Invalid or not attached team IDs: ' . implode(', ', $invalidIds)], 422);
        }

        $service->teams()->detach($existingAttachedTeamIds);
        return $service->load('teams');
    }
}
