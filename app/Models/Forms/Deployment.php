<?php

namespace App\Models\Forms;

use App\Enums\FormEnum;
use App\Models\ApprovalStatus;
use App\Models\Attachment;
use App\Models\BusinessExpert;
use App\Models\Form;
// use App\Models\FormDependencies\UatScenario;
use App\Models\FormDependencies\DeploymentDetail;
use App\Models\Project;
// use App\Models\SoftwareCategory;
// use App\Models\SoftwareSubcategory;
use App\Traits\CommonFormRelationships;
use App\Traits\Table;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deployment extends Model
{
    use HasFactory, SoftDeletes, Table, CommonFormRelationships;
    protected $table = "deployments";
    protected $fillable = [
        'sequence_no',
        'request_title',
        'change_priority',
        'project_id',
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
        'reference_form_id',
        'reference_details',
        'status',
        'attachments',
        'deployment_status',
    ];

    public function qualityAssurables(): MorphMany
    {
        return $this->morphMany(QualityAssurance::class, 'assurable');
    }

    public function business_expert(): BelongsTo
    {
        return $this->belongsTo(BusinessExpert::class);
    }

    public function referenceForm(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'reference_form_id', 'id');
    }

    // public function referenceDetail()
    // {
    //     $form = $this->referenceForm;
    //     if (!$form) {
    //         return $this->belongsTo(Form::class, 'reference_details')->whereNull('id');
    //     }

    //     if (empty($form->identity)) {
    //         return $this->belongsTo(Form::class, 'reference_details')->whereNull('id');
    //     }

    //     if (!class_exists($form->identity)) {
    //         return $this->belongsTo(Form::class, 'reference_details')->whereNull('id');
    //     }

    //     return $this->belongsTo($form->identity, 'reference_details');
    // }
    public function referenceDetail(): BelongsTo
    {
        if (is_null($this->reference_form_id)) {
            return $this->belongsTo(Form::class, 'reference_details')->whereNull('id');
        }

        $modelClass = FormEnum::getModelById($this->reference_form_id);

        if (!class_exists($modelClass)) {
            return $this->belongsTo(Form::class, 'reference_details')->whereNull('id');
        }

        return $this->belongsTo($modelClass, 'reference_details');
    }

    public function deploymentDetail(): HasMany
    {
        return $this->hasMany(DeploymentDetail::class, 'deployment_id', 'id');
    }

    public function qualityAssurances(): HasMany
    {
        return $this->hasMany(QualityAssurance::class, 'scrf_id', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
