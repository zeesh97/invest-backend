<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Models\SetupField;
use App\Http\Requests\StoreSetupFieldRequest;
use App\Http\Requests\UpdateSetupFieldRequest;
use App\Http\Resources\SetupFieldResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SetupFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return SetupFieldResource::collection(SetupField::latest()->paginate());

        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    public function all()
    {
        try {
            $setupFields = SetupField::latest()->get(['id','name','identity']);
            return $setupFields;
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreSetupFieldRequest $request): Response
    // {
    //     //
    // }

    public function show(int $id): JsonResponse
    {
        try {
            $form = SetupField::findOrFail($id);
            return Helper::sendResponse(new SetupFieldResource($form), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSetupFieldRequest $request, SetupField $setupField): Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(SetupField $setupField): Response
    // {
    //     //
    // }
}
