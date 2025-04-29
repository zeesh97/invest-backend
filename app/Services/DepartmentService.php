<?php

namespace App\Services;

// use App\Http\Resources\LoginUserResource;

use App\Http\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DepartmentService
{
    public function index(Request $request): JsonResponse
    {
        try {
            if ($request->has('all')) {
                $departments = Department::with(['services:id,name'])->select(['id', 'name'])->latest()->get();
                return Helper::sendResponse($departments, 'Success');
            } else {
                $perPage = $request->get('per_page', default: 10);
                $departments = Department::with(['services:id,name'])
                    ->select(['id', 'name'])
                    ->latest()
                    ->paginate($perPage);

                return Helper::sendResponse($departments, 'Success', 200);
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }
    public function show(int $id): JsonResponse
    {
        try {
            $department = Department::with(['services:id,name'])
                ->select(['id', 'name'])
                ->find($id);

            return Helper::sendResponse($department, 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }
    public function attachServices(Request $request, Department $department)
    {
        $request->validate([
            'service_ids' => 'required|array',
        ]);

        $serviceIds = $request->input('service_ids');

        // Check if all provided service IDs exist efficiently
        $existingServiceIds = DB::table('services')
            ->whereIn('id', $serviceIds)
            ->pluck('id')
            ->toArray();

        // Find the difference between provided and existing IDs
        $invalidIds = array_diff($serviceIds, $existingServiceIds);

        if (!empty($invalidIds)) {
            return Helper::sendError('Invalid service IDs: ',  implode(', ', $invalidIds), 422);
        }


        $department->services()->sync($existingServiceIds);
        return $department->load('services');
    }

    public function detachServices(Request $request, Department $department)
    {
        $request->validate([
            'service_ids' => 'required|array',
        ]);

        $serviceIds = $request->input('service_ids');

        $existingAttachedServiceIds = DB::table('department_service')
            ->where('department_id', $department->id)
            ->whereIn('service_id', $serviceIds)
            ->pluck('service_id')
            ->toArray();


        $invalidIds = array_diff($serviceIds, $existingAttachedServiceIds);

        if (!empty($invalidIds)) {
            return response()->json(['error' => 'Invalid or not attached service IDs: ' . implode(', ', $invalidIds)], 422);
        }

        $department->services()->detach($existingAttachedServiceIds);
        return $department->load('services');
    }
}
