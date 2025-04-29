<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Approver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'approver_users')
            ->withPivot('approval_required', 'sequence_no');
    }

    public function approvalStatuses()
    {
        return $this->hasMany(ApprovalStatus::class, 'approver_id');
    }

    public function workflows(): BelongsToMany
    {
        return $this->belongsToMany(Workflow::class, 'workflow_subscribers_approvers', 'approver_id', 'workflow_id');
    }
}
