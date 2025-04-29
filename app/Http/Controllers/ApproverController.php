<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\ApproverResource;
use App\Models\Approver;
use App\Models\ApproverUser;
use App\Models\Workflow;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ApproverController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:Approvers-create|Approvers-edit|Approvers-delete', ['only' => ['users', 'show']]);
            $this->middleware('role_or_permission:Approvers-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Approvers-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Approvers-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:Approvers-create|Approvers-edit|Approvers-delete', ['only' => ['users', 'show']]);
            $this->middleware('role_or_permission:Approvers-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Approvers-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Approvers-delete', ['only' => ['destroy']]);
        }
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(Approver::latest()->select(['id', 'name'])->get(), 'Success');
            } else {
                return ApproverResource::collection(Approver::latest()->paginate());
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_CREATED);
        }
    }
    public function all()
    {
        try {
            $approvers = Approver::latest()->get(['id', 'name']);
            return $approvers;
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_CREATED);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:approvers,name',
                'users' => 'required|array',
                'users.*' => 'exists:users,id',
                'approval_required' => 'required|array',
                'approval_required.*' => 'boolean',
                'sequence_no' => 'required|array',
                'sequence_no.*' => 'integer|min:1',
            ]);

            $sequenceNo = $request->input('sequence_no');

            if (count($sequenceNo) !== count(array_unique($sequenceNo))) {
                return Helper::sendError('Sequence numbers must be unique.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            sort($sequenceNo);
            $expectedSequence = range(1, count($sequenceNo));
            if ($sequenceNo !== $expectedSequence) {
                return Helper::sendError('Sequence numbers must be consecutive starting from 1 without gaps.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $approver = Approver::create([
                'name' => $request->input('name'),
            ]);

            $users = $request->input('users');
            $approvalRequired = $request->input('approval_required');
            $sequenceNo = $request->input('sequence_no'); // Re-fetch original order

            foreach ($users as $index => $userId) {
                $approver->users()->attach($userId, [
                    'approval_required' => $approvalRequired[$index],
                    'sequence_no' => $sequenceNo[$index],
                ]);
            }

            return Helper::sendResponse($approver, 'Successfully Added', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $approver = Approver::findOrFail($id);
            return Helper::sendResponse(new ApproverResource($approver), 'Success', 200);
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
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('approvers')->ignore($id),
                ],
                'users' => 'required|array',
                'users.*' => 'exists:users,id',
                'approval_required' => 'required|array',
                'approval_required.*' => 'boolean',
                'sequence_no' => 'required|array',
                'sequence_no.*' => 'integer|min:1',
            ]);

            $sequenceNo = $request->input('sequence_no');

            // Check for duplicate sequence numbers
            if (count($sequenceNo) !== count(array_unique($sequenceNo))) {
                return Helper::sendError('Sequence numbers must be unique.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Check if sequence numbers form a consecutive range starting from 1
            $sortedSequence = $sequenceNo;
            sort($sortedSequence);
            $expectedSequence = range(1, count($sortedSequence));
            if ($sortedSequence !== $expectedSequence) {
                return Helper::sendError('Sequence numbers must be consecutive starting from 1 without gaps.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $approver = Approver::findOrFail($id);

            $approver->update([
                'name' => $request->input('name'),
            ]);

            $users = $request->input('users');
            $approvalRequired = $request->input('approval_required');

            // Detach existing users and sync with new data
            $syncData = [];
            foreach ($users as $index => $userId) {
                $syncData[$userId] = [
                    'approval_required' => $approvalRequired[$index],
                    'sequence_no' => $sequenceNo[$index],
                ];
            }

            $approver->users()->sync($syncData);

            return Helper::sendResponse([
                'result' => new ApproverResource($approver),
                'message' => 'Approver updated successfully.',
                'status' => Response::HTTP_CREATED,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update approver.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }






    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $approver = Approver::findOrFail($id);
            if ($approver) {
                $workflow = Workflow::where('approvers_id', $id)->exists();
                if (!$workflow) {
                    // $approver->users()->detach();
                    $approver->delete();

                    return response()->json([
                        'message' => 'Approver deleted successfully.',
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete approver. May be already assigned.',
            ], 404);
        }
    }
}
