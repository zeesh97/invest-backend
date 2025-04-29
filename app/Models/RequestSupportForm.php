<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class RequestSupportForm extends Model
{

    use HasFactory;
    protected $table = 'request_support_forms';
    protected $fillable = [
        'sequence_no',
        'request_title',
        'relevant_id',
        'priority',
        'phone',
        'department_id',
        'location_id',
        'service_id',
        'description',
        'task_status_at',
        'task_status',
        'save_as_draft',
        'attachments',
        'comment_status',
        'created_by'
    ];

    /**
     * Get the location that owns the RequestSupportForm
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the department that owns the RequestSupportForm
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_request_support_form');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function taskStatus(): BelongsTo
    {
        return $this->belongsTo(TaskStatusName::class, 'task_status');
    }

    public function assignedTask(): MorphOne
    {
        return $this->morphOne(AssignTask::class, 'assignable');
    }
    public function assignedTasks(): MorphMany
    {
        return $this->morphMany(AssignTask::class, 'assignable');
    }
    public function taskStatusName(): BelongsTo
    {
        return $this->belongsTo(TaskStatusName::class, 'task_status');
    }
    public function attachables(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'attachable_id', 'id');
    }
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
