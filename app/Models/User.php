<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Department;
use App\Models\Company;
use App\Models\Designation;
use App\Models\Location;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_no',
        'employee_type',
        'profile_photo_path',
        'department_id',
        'location_id',
        'designation_id',
        'section_id',
        'extension',
        'phone_number',
        'company_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function accessLevels(): MorphMany
    {
        return $this->morphMany(UserAccessLevel::class, 'accessible');
    }
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'causer_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function businessExpert(): BelongsTo
    {
        return $this->belongsTo(BusinessExpert::class, 'business_expert_user_id', 'id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'user_id', 'id');
    }

    public function approvers(): BelongsToMany
    {
        return $this->belongsToMany(Approver::class, 'approver_users')
            ->withPivot('approval_required', 'sequence_no');
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class, 'subscriber_user')
            ->withPivot('sequence_no');
    }

    public function approvalStatus(): HasMany
    {
        return $this->hasMany(ApprovalStatus::class, 'user_id', 'id');
    }

    public function workflowInitiators(): HasMany
    {
        return $this->hasMany(Workflow::class, 'initiator_id', 'id');
    }
    public function formRoles(): BelongsToMany
    {
        return $this->belongsToMany(FormRole::class, 'form_role_user');
    }

    public function hasFormRole($role)
    {
        $this->formRoles->contains('name', $role);
    }

    public function parallelApprovers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'approver_location_parallel_user', 'user_id', 'parallel_user_id')
            ->withPivot('location_id')
            ->withTimestamps();
    }
    public function teamMembers(): BelongsToMany
    {
        // return $this->belongsToMany(Team::class);
        return $this->belongsToMany(Team::class, 'team_member', 'member_id', 'team_id')
            ->withPivot(['form_id']);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'form_location_manager_team', 'manager_id', 'team_id')
            // ->withPivot('form_id');
            ->withPivot(['form_id', 'location_id', 'manager_id', 'team_id']);
    }

    public function userAccessLevels(): HasMany
    {
        return $this->hasMany(UserAccessLevel::class);
    }


    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function autoAssignTasks(): BelongsToMany
    {
        return $this->belongsToMany(AutoAssignTask::class, 'auto_assign_task_members', 'member_id', 'auto_assign_task_id'); // Relate to AutoAssignTask
    }
}
