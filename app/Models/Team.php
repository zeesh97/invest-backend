<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'form_location_manager_team', 'team_id', 'location_id');
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'form_location_manager_team', 'team_id', 'manager_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_member', 'team_id', 'member_id');
    }
    // public function members(): HasMany
    // {
    //     return $this->hasMany(User::class, 'id', 'member_id');
    // }

    public function forms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class, 'form_location_manager_team', 'team_id', 'form_id');
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(AssignTask::class, 'assign_task_team', 'team_id', 'assign_task_id')
            ->withPivot('member_id');
    }
    public function requestSupportForms(): BelongsToMany
    {
        return $this->belongsToMany(RequestSupportForm::class, 'team_request_support_form');
    }

}
