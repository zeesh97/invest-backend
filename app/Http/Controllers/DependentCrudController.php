<?php

namespace App\Http\Controllers;

use App\Enums\DependentCrudEnum;
use App\Http\Helpers\Helper;
use App\Http\Resources\IndexDependentCrudResource;
use App\Models\DependentCrud;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class DependentCrudController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => [
                'nullable',
                'string',
                'max:250',
                Rule::in(DependentCrudEnum::values())
            ],
            'company_id' => 'nullable|integer',
        ]);

        $query = DependentCrud::query()->with('company:id,name,logo');

        if (!empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }
        if (!empty($validated['company_id'])) {
            $query->where('company_id', $validated['company_id']);
        }

        $data = $query->get();

        return Helper::sendResponse(
            IndexDependentCrudResource::collection($data),
            'DependentCrud retrieved successfully',
            201
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        ini_set('max_input_vars', 10000);

        ini_set('max_multipart_body_parts', 10000);
        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                'max:250',
                Rule::in(DependentCrudEnum::values())
            ],
            'company_id' => 'required|integer|exists:companies,id',
            'parent_id' => 'nullable|integer',
            'data' => 'required|array',
            'data.*' => 'nullable|array',
            'data.*.value' => 'nullable|string',
            'data.*.description' => 'nullable|string',
        ]);

        // Filter out the data items where both 'value' and 'description' are null
        $validated['data'] = array_filter($validated['data'], function ($item) {
            return !is_null($item['value']) || !is_null($item['description']);
        });

        // Optionally, we can re-index the array if filtering out null items
        $validated['data'] = array_values($validated['data']);

        // Check if a record already exists based on the unique combination of 'type', 'company_id', and 'parent_id'
        $record = DependentCrud::where('type', $validated['type'])
            ->where('company_id', $validated['company_id'])
            ->where('parent_id', $validated['parent_id'])
            ->first();

        if ($record) {
            // Update the record if it exists
            $record->update([
                'data' => $validated['data'], // 'data' is already an array, so no need to encode it
            ]);
            return Helper::sendResponse($record, 'Dependent Crud updated successfully', 200);
        }

        // Create a new record if one doesn't exist
        $dependentCrud = DependentCrud::create([
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'],
            'company_id' => $validated['company_id'],
            'data' => $validated['data'], // 'data' is already processed and can be stored as is
        ]);

        return Helper::sendResponse($dependentCrud, 'Dependent Crud created successfully', 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(DependentCrud $dependentCrud): JsonResponse
    {
        return response()->json($dependentCrud);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DependentCrud $dependentCrud): JsonResponse
    {
        return response()->json($dependentCrud);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $dependentCrud = DependentCrud::find($id);

            // if (
            //     User::withTrashed()->where('department_id', $id)->exists() ||
            //     Section::withTrashed()->where('department_id', $id)->exists()
            // ) {
            //     return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            // } else {
            $dependentCrud->delete();
            return Helper::sendResponse($dependentCrud, 'Parameter deleted successfully');
            // }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
