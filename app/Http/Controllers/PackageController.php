<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    /**
     * Display a listing of the packages.
     */
    public function index(): JsonResponse
    {
        $packages = Package::all();
        return Helper::sendResponse($packages, 'Package list retrieved successfully', 200);
    }

    /**
     * Store a newly created package in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'number_of_transactions' => 'required|integer|min:0',
            'data_mb' => 'required|integer|min:0',
            'total_users' => 'required|integer|min:0',
            'login_users' => 'required|integer|min:0',
            'period_type_id' => 'required|exists:period_types,id',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();
            $package = Package::create($validated);
            DB::commit();

            return Helper::sendResponse($package, 'Package created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::sendError('Error creating package: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified package.
     */
    public function show($id): JsonResponse
    {
        $package = Package::find($id);
        if (!$package) {
            return Helper::sendError('Package not found', 404);
        }

        return Helper::sendResponse($package, 'Package retrieved successfully', 200);
    }

    /**
     * Update the specified package in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $package = Package::find($id);
        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'number_of_transactions' => 'sometimes|integer|min:1',
            'data_mb' => 'sometimes|integer|min:1',
            'total_users' => 'sometimes|integer|min:1',
            'login_users' => 'sometimes|integer|min:1',
            'period_type_id' => 'sometimes|exists:period_types,id',
            'is_active' => 'boolean',
        ]);


        try {
            DB::beginTransaction();
            $package->update($validated);
            DB::commit();

            return Helper::sendResponse($package, 'Package updated successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::sendError('Error updating package: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified package from storage.
     */
    public function destroy($id): JsonResponse
    {
        $package = Package::find($id);

        if (!$package) {
            return Helper::sendError('Package not found', 404);
        }

        try {
            DB::beginTransaction();
            $package->delete();
            DB::commit();

            return Helper::sendResponse(null, 'Package deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::sendError('Error deleting package: ' . $e->getMessage(), 500);
        }
    }
}
