<?php

namespace App\Actions;

use App\Events\AssignPermissionToUsers;
use App\Http\Helpers\Helper;
use App\Jobs\SendAssignedTaskEmailJob;
use App\Models\AssignTask;
use App\Models\AutoAssignTask;
use App\Models\Form;
use App\Models\Team;
use App\Models\User;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Log;

class AutoAssignTaskAction
{
    public function __construct() {}

    public static function handle(AutoAssignTask $autoAssignTask, Model $model, $modelName, int $userId): JsonResponse
    {
        $assignTask = DB::transaction(function () use ($autoAssignTask, $model, $modelName, $userId): void {

            $assignTask = AssignTask::create([
                'assignable_id' => $model->id,
                'assignable_type' => $modelName,
                'task_assigned_by' => $userId,
                'start_at' => now(),
                'due_at' => now()->addDays(14),
            ]);
            $autoAssignTask->load('teamMembers');
            $teamIds = $autoAssignTask->teamMembers()->pluck('team_id')->unique()->toArray();
            $members = $autoAssignTask->teamMembers()->pluck('member_id')->unique()->toArray();

            $teams = Team::with('managers:id,name')->whereIn('id', $teamIds)->get();

            // Map team managers
            $teamManagers = [];
            foreach ($teams as $team) {
                $teamManagers[$team->id] = $team->managers->pluck('id')->toArray();
            }
            foreach ($autoAssignTask->teamMembers as $teamItem) {
                $teamId = $teamItem['team_id'];
                $memberId = $teamItem['member_id'];

                $managers = $teamManagers[$teamId] ?? [];

                $allMemberIds = array_unique(array_merge([$memberId], $managers));

                foreach ($allMemberIds as $member) {
                    $assignTask->assignedTeams()->attach($teamId, ['member_id' => $member]);
                }
            }

            event(new AssignPermissionToUsers(array_merge($members, ...array_values($teamManagers)), $modelName, $model->id));
            dispatch(new SendAssignedTaskEmailJob(array_merge(...array_values($teamManagers)), $members, task: $assignTask->toArray()));
        });

        return Helper::sendResponse($assignTask, 'Task assigned to teams and members successfully!', 201);
    }
}
