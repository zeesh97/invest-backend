<?php

namespace App\Services;

use App\Http\Resources\AssignTaskResource;
use App\Models\AssignTask;

class AssignTaskService extends BaseIndexService
{
    public function __construct()
    {
        parent::__construct(AssignTask::class, AssignTaskResource::class);
    }

    protected function getFilters()
    {
        return [
            'sequence_no',
            'request_title',
        ];
    }

    protected function getRelationships()
    {
        return [
            'assignable' => 'form_data',
            'assignedTeams' => 'assigned_teams',
            'taskAssignedBy' => 'task_assigned_by',
        ];
    }
}
