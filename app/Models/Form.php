<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'identity',
        'slug',
        'initiator_field_one_id',
        'initiator_field_two_id',
        'initiator_field_three_id',
        'initiator_field_four_id',
        'initiator_field_five_id',
        'callback'
    ];

    public function initiator_field_one(): BelongsTo
    {
        return $this->belongsTo(SetupField::class, 'initiator_field_one_id', 'id');
    }
    public function initiator_field_two(): BelongsTo
    {
        return $this->belongsTo(SetupField::class, 'initiator_field_two_id', 'id');
    }
    public function initiator_field_three(): BelongsTo
    {
        return $this->belongsTo(SetupField::class, 'initiator_field_three_id', 'id');
    }
    public function initiator_field_four(): BelongsTo
    {
        return $this->belongsTo(SetupField::class, 'initiator_field_four_id', 'id');
    }
    public function initiator_field_five(): BelongsTo
    {
        return $this->belongsTo(SetupField::class, 'initiator_field_five_id', 'id');
    }

    public function workflow_initiator_fields(): HasMany
    {
        return $this->hasMany(WorkflowInitiatorField::class);
    }

    public function approvalStatus(): HasMany
    {
        return $this->hasMany(ApprovalStatus::class, 'form_id', 'id');
    }
    public function approvalStatusForms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class, 'approval_statuses', 'form_id', 'key')
            ->withPivot('approval_required', 'sequence_no', 'reason', 'status');
    }
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }
    public function conditions(): HasMany
    {
        return $this->hasMany(Condition::class);
    }
}
