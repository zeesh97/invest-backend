<?php

namespace App\Http\Controllers;

use App\Events\AssignPermissionToUsers;
use App\Http\Helpers\Helper;
use App\Http\Requests\StoreAssignTaskRequest;
use App\Http\Requests\UpdateAssignTaskRequest;
use App\Http\Resources\AssignTaskResource;
use App\Jobs\SendAssignedTaskEmailJob;
use App\Models\AssignTask;
use App\Models\AssignTaskTeam;
use App\Models\Form;
use App\Models\Team;
use App\Services\GlobalFormService;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AssignTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:AssignTask-view', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:AssignTask-create', ['only' => ['store']]);
            $this->middleware('role_or_permission:AssignTask-edit', ['only' => ['update']]);
        }
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(AssignTask::latest()->select(['id', 'request_title'])->get(), 'Success');
            }
            $rules = [
                'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
                'sortBy' => ['nullable', 'string'],
                'sortOrder' => ['nullable', 'in:asc,desc']
            ];

            $validated = $request->validate($rules);
            $perPage = $validated['perPage'] ?? 10;
            $sortBy = $validated['sortBy'] ?? 'created_at';
            $sortOrder = $validated['sortOrder'] ?? 'desc';

            if (Auth::user()->hasRole('admin')) {
                return AssignTaskResource::collection(
                    AssignTask::orderBy($sortBy, $sortOrder)->paginate($perPage)
                );
            }

            return AssignTaskResource::collection(
                AssignTask::whereHas('assignTaskTeams', function ($query) {
                    $query->where('member_id', Auth::user()->id);
                })->orderBy($sortBy, $sortOrder)->paginate($perPage)
            );
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAssignTaskRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // Verify form exists and get assignable type
            $form = Form::findOrFail($validated['form_id']);
            $assignableType = $form->identity;

            // Check for existing task assignment
            $existingTask = AssignTask::where('assignable_id', $validated['key'])
                ->where('assignable_type', $assignableType)
                ->exists();

            if ($existingTask) {
                return Helper::sendError('This task is already assigned to a team and members.', [], 403);
            }

            // Create the main task assignment
            $assignTask = AssignTask::create([
                'assignable_id' => $validated['key'],
                'assignable_type' => $assignableType,
                'task_assigned_by' => Auth::user()->id,
                'start_at' => $validated['start_at'] ? $validated['start_at'] : now(),
                'due_at' => $validated['due_at'] ? $validated['due_at'] : now()->addDays(14),
            ]);

            // Process teams and members
            $teamMemberMap = [];
            $allMemberIds = [];

            foreach ($validated['team_ids'] as $teamItem) {
                if (!isset($teamItem['team_id'])) {
                    throw new \InvalidArgumentException('Invalid team data structure: missing team_id');
                }

                $teamId = $teamItem['team_id'];
                $memberIds = $teamItem['team_members'];

                $team = Team::with('managers')->findOrFail($teamId);
                $managerIds = $team->managers->pluck('id')->toArray();

                $combinedIds = array_unique(array_merge($memberIds, $managerIds));

                foreach ($combinedIds as $memberId) {
                    $teamMemberMap[] = [
                        'assign_task_id' => $assignTask->id,
                        'team_id' => $teamId,
                        'member_id' => $memberId,
                        'start_at' => null,
                        'due_at' => null,
                    ];
                }

                $allMemberIds = array_merge($allMemberIds, $combinedIds);

                dispatch(new SendAssignedTaskEmailJob(
                    $managerIds,
                    $memberIds,
                    $assignTask->fresh()->toArray()
                ));
            }


            // Bulk insert team assignments
            DB::table('assign_task_team')->insert($teamMemberMap);

            // Dispatch permission event once for all members
            event(new AssignPermissionToUsers(
                array_unique($allMemberIds),
                $assignableType,
                $validated['key']
            ));

            DB::commit();

            return Helper::sendResponse(
                $assignTask->load('assignedTeams'),
                'Task assigned to teams and members successfully!',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Task assignment failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'user' => Auth::user()->id
            ]);

            return Helper::sendError(
                'Failed to assign task. Please try again.',
                [],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(AssignTask $assignTask): JsonResponse
    {
        if ($assignTask) {
            return Helper::sendResponse(new AssignTaskResource($assignTask), 'Success', 200);
        }
        return Helper::sendError('Cannot find this record.', [], Response::HTTP_NOT_FOUND);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAssignTaskRequest $request, $id): JsonResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $form = Form::findOrFail($validated['form_id']);
            $assignableType = $form->identity;

            // Find existing assignment
            $assignTask = AssignTask::findOrFail($id);

            // Update main task fields
            $assignTask->update([
                'start_at' => $validated['start_at'],
                'due_at' => $validated['due_at'],
            ]);

            // Delete previous team assignments
            DB::table('assign_task_team')
                ->where('assign_task_id', $assignTask->id)
                ->delete();

            $teamMemberMap = [];
            $allMemberIds = [];

            foreach ($validated['team_ids'] as $teamItem) {
                $teamId = $teamItem['team_id'];
                $memberIds = $teamItem['team_members'];

                $team = Team::with('managers')->findOrFail($teamId);
                $managerIds = $team->managers->pluck('id')->toArray();

                $combinedIds = array_unique(array_merge($memberIds, $managerIds));

                foreach ($combinedIds as $memberId) {
                    $teamMemberMap[] = [
                        'assign_task_id' => $assignTask->id,
                        'team_id' => $teamId,
                        'member_id' => $memberId,
                        'start_at' => null,
                        'due_at' => null,
                    ];
                }

                $allMemberIds = array_merge($allMemberIds, $combinedIds);

                dispatch(new SendAssignedTaskEmailJob(
                    $managerIds,
                    $memberIds,
                    $assignTask->fresh()->toArray()
                ));
            }

            // Insert updated assignments
            DB::table('assign_task_team')->insert($teamMemberMap);

            // Re-assign permissions
            event(new AssignPermissionToUsers(
                array_unique($allMemberIds),
                $assignableType,
                $validated['key']
            ));

            DB::commit();

            return Helper::sendResponse(
                $assignTask->load('assignedTeams'),
                'Task assignment updated successfully!',
                200
            );
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Task assignment update failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'user' => Auth::user()->id
            ]);

            return Helper::sendError(
                'Failed to update task assignment. Please try again.',
                [],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(AssignTask $assignTask): Response
    // {
    //     //
    // }

    public function updateTaskStatus(Request $request, GlobalFormService $updateTaskStatus)
    {
        $validated = $request->validate([
            'task_id' => ['required', 'exists:assign_tasks,id'],
            'form_id' => ['required', 'exists:forms,id'],
            'team_id' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = DB::table('assign_task_team')
                        ->where('team_id', $value)
                        ->where('member_id', Auth::user()->id)
                        ->where('assign_task_id', $request->input('task_id'))
                        ->exists();

                    if (!$exists) {
                        $fail('The selected team is invalid.');
                    }
                },
            ],
            'status' => ['required', 'exists:task_status_names,id'],
            'start_at' => 'nullable|date_format:Y-m-d H:i:s',
            'due_at' => 'sometimes|date_format:Y-m-d H:i:s|after_or_equal:start_at',
        ]);

        $assignTask = AssignTask::whereHas('assignTaskTeams', function ($query) use ($request) {
            $query->where('member_id', Auth::user()->id)
                ->where('team_id', $request->input('team_id'));
        })->find($request->task_id);
        DB::beginTransaction();

        try {
            if (!is_null($assignTask) && !is_null($assignTask->assignable)) {

                $result = DB::table($assignTask->assignable->getTable())->where('id', $assignTask->assignable->id)->update([

                    'task_status_at' => Carbon::now(),
                    'task_status' => $request->status
                ]);
                AssignTaskTeam::where('assign_task_id', $assignTask->id)
                    ->where('member_id', Auth::user()->id)
                    ->update([
                        'start_at' => $validated['start_at'] ?? now(),
                        'due_at' => $validated['due_at'] ?? now(),
                    ]);
                if ($result) {
                    DB::commit();
                    return Helper::sendResponse($result, 'Task status updated successfully!', 201);
                }
            }
            return Helper::sendError('Record not found.', [], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::sendError('Failed to update status.', ['error' => $e->getMessage()], 500);
        }
    }
    public function updateTaskStatusNonForm(Request $request, GlobalFormService $updateTaskStatus)
    {
        $validated = $request->validate([
            'task_id' => ['required', 'exists:assign_tasks,id'],
            'non_form_id' => ['required', 'numeric'],
            'status' => ['required', 'exists:task_status_names,id'],
        ]);

        $assignTask = AssignTask::whereHas('assignTaskTeams', function ($query) {
            $query->where('member_id', Auth::user()->id);
        })->find($request->task_id);

        // dd($assignTask);

        if (!is_null($assignTask) && !is_null($assignTask->assignable)) {

            $result = DB::table($assignTask->assignable->getTable())->where('id', $assignTask->assignable->id)->update([

                'start_at' => $validated['start_at'],
                'due_at' => $validated['due_at'],
                'task_status_at' => Carbon::now(),
                'task_status' => $request->status
            ]);
            if ($result) {
                return Helper::sendResponse($result, 'Task status updated successfully!', 201);
            }
        }
        return Helper::sendError('Record not found.', [], 404);
    }
}
