<?php

namespace App\Models\Forms;

use App\Models\ApprovalStatus;
use App\Models\Attachment;
use App\Models\BusinessExpert;
use App\Models\FormDependencies\UatScenario;
use App\Models\SoftwareCategory;
use App\Models\SoftwareSubcategory;
use App\Traits\CommonFormRelationships;
use App\Traits\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SCRF extends Model
{
    use HasFactory, SoftDeletes, Table, CommonFormRelationships;
    protected $table = "scrf";
    protected $fillable = [
        'sequence_no',
        'request_title',
        'request_specs',
        'change_type',
        'change_priority',
        'process_efficiency',
        'controls_improved',
        'man_hours',
        'cost_saved',
        'legal_reasons',
        'other_benefits',
        'change_significance',
        'software_category_id',
        'software_subcategory_id',
        'location_id',
        'department_id',
        'designation_id',
        'section_id',
        'created_by',
        'updated_by',
        'workflow_id',
        'draft_at',
        'comment_status',
        'status',
        'attachments'
    ];

    public function qualityAssurables(): MorphMany
    {
        return $this->morphMany(QualityAssurance::class, 'assurable');
    }

    // public function workflow_initiator_field(): BelongsTo
    // {
    //     return $this->belongsTo(WorkflowInitiatorField::class);
    // }

    public function software_subcategories(): BelongsToMany
    {
        return $this->belongsToMany(SoftwareSubcategory::class, 'scrf_software_subcategory', 'scrf_id', 'software_subcategory_id');
    }

    public function software_category(): BelongsTo
    {
        return $this->belongsTo(SoftwareCategory::class);
    }

    public function business_expert(): BelongsTo
    {
        return $this->belongsTo(BusinessExpert::class);
    }

    public function uatScenarios(): HasMany
    {
        return $this->hasMany(UatScenario::class, 'scrf_id', 'id');
    }

    public function qualityAssurances(): HasMany
    {
        return $this->hasMany(QualityAssurance::class, 'scrf_id', 'id');
    }

    public function approvalStatusSCRF(): BelongsToMany
    {
        return $this->belongsToMany(SCRF::class, 'approval_statuses', 'key', 'form_id')
            ->withPivot('approval_required', 'sequence_no', 'reason', 'status');
    }
}
