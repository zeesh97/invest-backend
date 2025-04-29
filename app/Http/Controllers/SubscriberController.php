<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\SubscriberResource;
use App\Models\Subscriber;
use App\Models\Workflow;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class SubscriberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(): JsonResponse
    // {
    //     try {
    //         $subscribers = Subscriber::latest()->paginate();


    //         return response()->json([
    //             'data' => $subscribers,
    //         ]);
    //     } catch (\Exception $e) {
    //         return Helper::sendError($e->getMessage());
    //     }
    // }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(Subscriber::latest()->select(['id', 'name'])->get(), 'Success');
            } else {
                return SubscriberResource::collection(Subscriber::latest()->paginate());
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): SubscriberResource
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:subscribers,name',
                'users' => 'required|array',
                'users.*' => 'exists:users,id'
            ]);

            $subscriber = Subscriber::create([
                'name' => $request->input('name'),
            ]);

            $users = $request->input('users');
            foreach ($users as $index => $userId) {
                $subscriber->users()->attach($userId);
            }
            if (!$subscriber) {
                return Helper::sendError('Cannot create subscriber.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            return new SubscriberResource($subscriber);
        } catch (ValidationException $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     */

    public function show(int $id): JsonResponse
    {
        try {
            $form = Subscriber::findOrFail($id);
            return Helper::sendResponse(new SubscriberResource($form), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('subscribers')->ignore($id),
                ],
                'users' => 'required|array',
                'users.*' => 'exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors(),
                ], 422);
            }

            $subscriber = Subscriber::findOrFail($id);

            $subscriber->update([
                'name' => $request->input('name'),
            ]);

            $users = $request->input('users');

            // Sync the users without approval_required and sequence_no fields
            $syncData = [];
            foreach ($users as $index => $userId) {
                $syncData[$userId] = [];
            }
            $subscriber->users()->sync($syncData);

            return response()->json([
                'success' => true,
                'message' => 'Subscriber updated successfully.',
                'data' => new SubscriberResource($subscriber),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subscriber.',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $subscriber = Subscriber::withTrashed()->find($id);

            if (
                DB::table('subscriber_user')->where('subscriber_id', $id)->exists() ||
                DB::table('workflow_subscribers_approvers')->where('subscriber_id', $id)->exists()
                ) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $subscriber->delete();
            return Helper::sendResponse($subscriber, 'Subscriber deleted successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
