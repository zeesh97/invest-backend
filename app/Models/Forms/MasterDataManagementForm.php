<?php

namespace App\Models\Forms;

use App\Models\Attachment;
use App\Models\BusinessExpert;
use App\Models\FormDependencies\UatScenarioMDM;
use App\Models\MdmCategory;
use App\Models\ProjectMDM;
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

class MasterDataManagementForm extends Model
{
    use HasFactory, SoftDeletes, Table, CommonFormRelationships;
    protected $table = "master_data_management_forms";
    protected $fillable = [
        'sequence_no',
        'request_title',
        'mdm_project_id',
        'request_specs',
        'change_priority',
        'mdm_category_id',
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
    public function software_subcategories(): BelongsToMany
    {
        return $this->belongsToMany(SoftwareSubcategory::class, 'mdm_software_subcategory', 'master_data_management_form_id', 'software_subcategory_id');
    }

    public function software_category(): BelongsTo
    {
        return $this->belongsTo(SoftwareCategory::class);
    }
    public function mdm_category(): BelongsTo
    {
        return $this->belongsTo(MdmCategory::class);
    }
    public function projectMDM(): BelongsTo
    {
        return $this->belongsTo(ProjectMDM::class, 'mdm_project_id')
            ->with([
                'mdmCategory:id,name',
                'softwareCategory:id,name'
            ]);
    }

    public function business_expert(): BelongsTo
    {
        return $this->belongsTo(BusinessExpert::class);
    }

    public function uatScenarios(): HasMany
    {
        return $this->hasMany(UatScenarioMDM::class, 'master_data_management_form_id', 'id');
    }


    public function qualityAssurances(): HasMany
    {
        return $this->hasMany(QualityAssurance::class, 'master_data_management_form_id', 'id');
    }

    public function approvalStatusMDM(): BelongsToMany
    {
        return $this->belongsToMany(MasterDataManagementForm::class, 'approval_statuses', 'key', 'form_id')
            ->withPivot('approval_required', 'sequence_no', 'reason', 'status');
    }
}
