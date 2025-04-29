<?php

namespace App\Models\Forms;

use App\Models\Attachment;
use App\Models\User;
use App\Models\Make;
use App\Traits\CommonFormRelationships;
use App\Traits\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MobileRequisition extends Model
{
    use HasFactory, SoftDeletes, Table, CommonFormRelationships;
    protected $table = "mobile_requisitions";
    protected $fillable = [
        'sequence_no',
        'request_title',
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
        'request_for_user_id',
        'issue_date',
        'recieve_date',
        'make',
        'model',
        'imei',
        'mobile_number',
        // 'employee_code',
        'remarks'
    ];

    public function attachables(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function request_for_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'request_for_user_id');
    }

    public function makeRelation(): BelongsTo
    {
        return $this->belongsTo(Make::class, 'make');
    }

}
