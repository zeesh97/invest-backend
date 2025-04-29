<?php

namespace App\Http\Controllers;

use App\Enums\FormEnum;
use App\Events\AssignPermissionToUsers;
use App\Http\Helpers\Helper;
use App\Http\Resources\ParallelApproverResource;
use App\Models\ApprovalStatus;
use App\Models\ParallelApprover;
use App\Models\User;
use Auth;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ParallelApproverController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:ParallelApprover-view|ParallelApprover-create|ParallelApprover-edit|ParallelApprover-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:ParallelApprover-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:ParallelApprover-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:ParallelApprover-delete', ['only' => ['destroy']]);
        }
        // else {
        //     $this->middleware('role_or_permission:ParallelApprover-view|ParallelApprover-create|ParallelApprover-edit|ParallelApprover-delete', ['only' => ['index', 'show']]);
        //     $this->middleware('role_or_permission:ParallelApprover-create', ['only' => ['create', 'store']]);
        //     $this->middleware('role_or_permission:ParallelApprover-edit', ['only' => ['edit', 'update']]);
        //     $this->middleware('role_or_permission:ParallelApprover-delete', ['only' => ['destroy']]);
        // }
    }

    public function index()
    {
        try {
            if (!Auth::user()->hasRole('admin')) {
                return ParallelApproverResource::collection(
                    ParallelApprover::with(['user', 'parallelUser'])->where('user_id', Auth::user()->id)->latest()->paginate()
                );
            } else {
                return ParallelApproverResource::collection(ParallelApprover::with(['user', 'parallelUser'])->latest()->paginate());
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 403);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => ['required', 'exists:users,id', function ($attribute, $value, $fail) {
                    if (!Auth::user()->hasRole('admin') && $value != Auth::user()->id) {
                        $fail('No record found!');
                    }
                }],
                'location_id' => ['nullable', 'exists:locations,id'],
                'parallel_user_id' => [
                    'required',
                    'exists:users,id',
                    'different:user_id',
                    function ($attribute, $value, $fail) use ($request) {
                        $exists = DB::table('approver_location_parallel_user')
                            ->where('user_id', $request->input('user_id'))
                            ->where('parallel_user_id', $value)
                            // ->where('location_id', $request->input('location_id'))
                            ->exists();
                        if ($exists) {
                            $fail('The combination of current user, parallel user, and location already defined.');
                        }
                    }
                ],
            ]);
            $user = User::findOrFail($validated['user_id']);
            $user->parallelApprovers()->syncWithoutDetaching($validated['parallel_user_id']);

            ApprovalStatus::where('user_id', $validated['user_id'])
                ->get()
                ->map(function ($record) {
                    return [
                        'model_reference' => FormEnum::getModelById($record->form_id),
                        'key' => $record->key,
                    ];
                })
                ->each(function ($item) use ($validated) {
                    event(new AssignPermissionToUsers([$validated['parallel_user_id']], $item['model_reference'], $item['key']));
                });

            return Helper::sendResponse([], 'Success', 201);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 403);
        }
    }


    public function show(string $id): JsonResponse
    {
        $parallelApprover = DB::table('approver_location_parallel_user')->find($id);

        if (!$parallelApprover) {
            return Helper::sendError('Record not found', [], 404);
        }

        if (!Auth::user()->hasRole('admin') && $parallelApprover->user_id !== Auth::user()->id) {
            return Helper::sendError('Unauthorized', [], 403);
        }

        return Helper::sendResponse($parallelApprover, 'Success', 200);
    }



    public function destroy($id): JsonResponse
    {
        try {
            $parallelApprover = DB::table('approver_location_parallel_user')->find($id);

            if (!$parallelApprover) {
                return Helper::sendError('Record not found', [], 404);
            }

            if (!Auth::user()->hasRole('admin') && $parallelApprover->user_id !== Auth::user()->id) {
                return Helper::sendError('Unauthorized', [], 403);
            }

            DB::table('approver_location_parallel_user')->where('id', $id)->delete();

            return Helper::sendResponse([], 'Successfully deleted', 204);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 403);
        }
    }
}
