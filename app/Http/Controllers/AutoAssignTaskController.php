<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\AutoAssignTaskResource;
use App\Http\Resources\IndexAutoAssignTaskResource;
use App\Models\AutoAssignTask;
use App\Models\AutoAssignTaskTeamMemberPivot;
use Auth;
use DB;
use Google\Service\Compute\Help;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class AutoAssignTaskController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:AutoAssignTask-view|AutoAssignTask-create|AutoAssignTask-edit|AutoAssignTask-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:AutoAssignTask-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:AutoAssignTask-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:AutoAssignTask-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:AutoAssignTask-view|AutoAssignTask-create|AutoAssignTask-edit|AutoAssignTask-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:AutoAssignTask-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:AutoAssignTask-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:AutoAssignTask-delete', ['only' => ['destroy']]);
        }
    }
    /**
     * Display a listing of the auto-assigned tasks.
     */
    public function index(): JsonResponse
    {
        $tasks = AutoAssignTask::with([
            'user:id,name',
            'form:id,name',
            'teamMembers',
            'teamMembers.member:id,name,email,employee_no',
            'teamMembers.team:id,name',
        ])
            ->paginate(10);

        return Helper::sendResponse(
            [
                'tasks' => IndexAutoAssignTaskResource::collection($tasks->items()),
                'meta' => [
                    'current_page' => $tasks->currentPage(),
                    'from' => $tasks->firstItem(),
                    'last_page' => $tasks->lastPage(),
                    'per_page' => $tasks->perPage(),
                    'to' => $tasks->lastItem(),
                    'total' => $tasks->total(),
                ],
                'links' => [
                    'first' => $tasks->url(1),
                    'last' => $tasks->url($tasks->lastPage()),
                    'prev' => $tasks->previousPageUrl(),
                    'next' => $tasks->nextPageUrl(),
                ],
            ],
            'Task list retrieved successfully'
        );
    }

    /**
     * Store a newly created auto-assigned task.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'user_id' => 'required|exists:users,id',
            'teams' => 'required|array|min:1',
            'teams.*.team_id' => 'required|exists:teams,id',
            'teams.*.member_ids' => 'required|array|min:1',
            'teams.*.member_ids.*' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $task = AutoAssignTask::where('form_id', $validatedData['form_id'])
                ->where('user_id', $validatedData['user_id'])
                ->first();

            if ($task) {
                return Helper::sendResponse($task, 'This task is already assigned to the user.', Response::HTTP_CONFLICT);
            }

            $task = AutoAssignTask::create([
                'form_id' => $validatedData['form_id'],
                'user_id' => $validatedData['user_id']
            ]);

            $taskTeamMembers = [];
            foreach ($validatedData['teams'] as $team) {
                foreach ($team['member_ids'] as $memberId) {
                    $taskTeamMembers[] = [
                        'auto_assign_task_id' => $task->id,
                        'team_id' => $team['team_id'],
                        'member_id' => $memberId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            AutoAssignTaskTeamMemberPivot::insert($taskTeamMembers);

            DB::commit();
            return Helper::sendResponse($task, 'Task assigned successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return Helper::sendError('This task is already assigned to the user.', $e->getMessage(), Response::HTTP_CONFLICT);
            }

            return Helper::sendError('Failed to assign task', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a single task with assigned team members.
     */
    public function show($id)
    {
        $task = AutoAssignTask::with([
            'user:id,name',
            'form:id,name',
            'teamMembers',
            'teamMembers.member:id,name,email,employee_no',
            'teamMembers.team:id,name',
        ])->find($id);

        if (!$task) {
            return Helper::sendError('Task not found', [], Response::HTTP_NOT_FOUND);
        }

        return Helper::sendResponse(new AutoAssignTaskResource($task), 'Task details retrieved successfully');
    }

    /**
     * Update an auto-assigned task.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'user_id' => 'required|exists:users,id',
            'teams' => 'required|array|min:1',
            'teams.*.team_id' => 'required|exists:teams,id',
            'teams.*.member_ids' => 'required|array|min:1',
            'teams.*.member_ids.*' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $task = AutoAssignTask::findOrFail($id);

            $task->update([
                'form_id' => $validatedData['form_id'],
                'user_id' => $validatedData['user_id']
            ]);

            AutoAssignTaskTeamMemberPivot::where('auto_assign_task_id', $task->id)->delete();

            $taskTeamMembers = [];
            foreach ($validatedData['teams'] as $team) {
                foreach ($team['member_ids'] as $memberId) {
                    $taskTeamMembers[] = [
                        'auto_assign_task_id' => $task->id,
                        'team_id' => $team['team_id'],
                        'member_id' => $memberId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            AutoAssignTaskTeamMemberPivot::insert($taskTeamMembers);

            DB::commit();
            return Helper::sendResponse($task, 'Task updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::sendError('Failed to update task', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete an auto-assigned task.
     */
    public function destroy($id)
    {
        $task = AutoAssignTask::find($id);

        if (!$task) {
            return Helper::sendError('Task not found', [], Response::HTTP_NOT_FOUND);
        }

        $task->delete();
        return Helper::sendResponse(null, 'Task deleted successfully', Response::HTTP_NO_CONTENT);
    }
}
