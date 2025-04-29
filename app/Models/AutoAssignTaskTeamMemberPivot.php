<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoAssignTaskTeamMemberPivot extends Model
{
    protected $table = 'auto_assign_task_team_members_pivot';

    protected $fillable = ['auto_assign_task_id', 'team_id', 'member_id'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(AutoAssignTask::class, 'auto_assign_task_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
