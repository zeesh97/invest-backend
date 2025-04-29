<?php

namespace App\Models;

use App\Http\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AssignTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_assigned_by',
        'assignable_id',
        'assignable_type',
        'start_at',
        'due_at'
    ];
    protected function getStartAtAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
    protected function getDueAtAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getCreatedAtAttribute($value): ?String
    {
        if (!$value) {
            return null;
        }

        $timezone = Helper::appTimezone();
        return Carbon::parse($value)->timezone($timezone)->format('d-m-Y h:m:s');
    }
    public function getUpdatedAtAttribute($value): ?String
    {
        if (!$value) {
            return null;
        }

        $timezone = Helper::appTimezone();
        return Carbon::parse($value)->timezone($timezone)->format('d-m-Y h:m:s');
    }
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }
    public function assignedTeams()
    {
        return $this->belongsToMany(Team::class, 'assign_task_team', 'assign_task_id', 'team_id')
            ->withPivot('member_id');
    }

    public function assignTaskTeams()
    {
        return $this->hasMany(AssignTaskTeam::class, 'assign_task_id');
    }
    public function taskAssignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'task_assigned_by');
    }
}
