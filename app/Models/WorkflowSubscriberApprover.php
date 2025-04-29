<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowSubscriberApprover extends Model
{
    use HasFactory;
    public $table = "workflow_subscribers_approvers";
    protected $fillable = ['workflow_id', 'approver_id', 'subscriber_id', 'approval_condition', 'sequence_no', 'editable'];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Approver::class, 'approver_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class, 'subscriber_id');
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class, 'approval_condition', 'id');
    }
}
