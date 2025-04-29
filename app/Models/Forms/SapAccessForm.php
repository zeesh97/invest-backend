<?php

namespace App\Models\Forms;

use App\Http\Helpers\LocationHelper;
use App\Http\Helpers\PlantHelper;
use App\Models\Company;
use App\Traits\CommonFormRelationships;
use App\Traits\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SapAccessForm extends Model
{
    use HasFactory, SoftDeletes, Table, CommonFormRelationships;
    protected  $table = 'sap_access_forms';
    protected  $fillable = [
        'sequence_no',
        'request_title',
        'data',
        'type',
        'sap_id',
        'roles_required',
        'tcode_required',
        'business_justification',
        'company_id',
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
        'attachments',
    ];
    protected $casts = [
        'data' => 'array',
    ];
    protected $appends = ['sap_location'];
    /**
     * Get the company that owns the SapAccessForm
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getSapLocationAttribute(): array|null
    {
        return LocationHelper::findById($this->location_id);
    }

    public function getPlantAttribute(): array|null
    {
        return PlantHelper::findById($this->plant_id);
    }

}
