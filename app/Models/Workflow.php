<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Workflow extends Model
{
    use HasFactory;
    protected $table = 'workflows';
    protected $fillable = ['name', 'created_by_id', 'callback_id'];

    public function workflowSubscribersApprovers(): HasMany
    {
        return $this->hasMany(WorkflowSubscriberApprover::class, 'workflow_id', 'id');
    }

    public function approvalStatuses(): HasMany
    {
        return $this->hasMany(ApprovalStatus::class, 'workflow_id', 'id');
    }

    public function workflowInitiatorField(): HasOne
    {
        return $this->hasOne(WorkflowInitiatorField::class, 'workflow_id', 'id');
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class, 'workflow_subscribers_approvers', 'workflow_id', 'subscriber_id');
    }

    public function approvers(): BelongsToMany
    {
        return $this->belongsToMany(Approver::class, 'workflow_subscribers_approvers', 'workflow_id', 'approver_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id', 'id');
    }

    public function callback(): BelongsTo
    {
        return $this->belongsTo(Callback::class, 'callback_id', 'id');
    }
}
