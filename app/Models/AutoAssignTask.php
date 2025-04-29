<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoAssignTask extends Model
{
    protected $table = "auto_assign_tasks";
    protected $fillable = ['form_id', 'user_id'];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(AutoAssignTaskTeamMemberPivot::class, 'auto_assign_task_id')
        ->with(['team:id,name', 'member:id,name']);
    }

    public function teamsWithMembers()
    {
        return $this->teamMembers
            ->groupBy('team_id')
            ->map(function ($teamMembers, $teamId) {
                $team = $teamMembers->first()->team;

                return [
                    'team' => [
                        'id' => $team->id,
                        'name' => $team->name,
                    ],
                    'members' => $teamMembers->map(function ($teamMember) {
                        return [
                            'id' => $teamMember->member->id,
                            'name' => $teamMember->member->name,
                        ];
                    })->values()->toArray(),
                ];
            })
            ->values();
    }
}
