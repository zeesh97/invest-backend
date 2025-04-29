<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Models\OtherDependent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Nette\Utils\Json;

class OtherDependentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            if ($request->has('all')) {
                $records = OtherDependent::latest()->select(['id', 'type', 'data'])->get();
                return Helper::sendResponse($records, 'Success', 200);
            } else {
                $perPage = $request->get('per_page', 10);
                $records = OtherDependent::select(['id', 'type', 'data'])
                    ->latest()
                    ->paginate($perPage);
                return Helper::sendResponse($records, 'Success', 200);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to fetch cost centers: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => [
                    'required',
                    'string',
                    'max:100'
                ],
                'data' => 'nullable|array',
            ]);
            $record = OtherDependent::where('type', $validated['type'])->first();

            if ($record) {
                $record->update(['data' => $validated['data']]);
                return Helper::sendResponse($record, 'Record updated successfully.', 200);
            }

            $record = OtherDependent::create([
                ['type' => $validated['type']],
                ['data' => $validated['data'] ?? null]
            ]);

            return Helper::sendResponse($record, $record->wasRecentlyCreated ? 'Record created successfully.' : 'Record updated successfully.', 200);
        } catch (\Throwable $th) {
            return Helper::sendError($th->getMessage(), [], 400);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|integer',
                'name' => 'nullable|string|max:50',
            ]);
            $record = OtherDependent::where('id', $validated['id'])
                ->orWhere('name', $validated['name'])
                ->first();

            if ($record) {
                return Helper::sendResponse($record, 'Success', 200);
            }

            return Helper::sendError('Record not found.', [], 404);
        } catch (\Throwable $th) {
            return Helper::sendError($th->getMessage(), [], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */


    /**
     * Remove the specified resource from storage.
     */
}
