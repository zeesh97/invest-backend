<?php

namespace App\Models\Forms;

use App\Enums\ExpenseNatureEnum;
use App\Enums\ExpenseTypeEnum;
use App\Http\Helpers\Helper;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\EquipmentRequest;
use App\Models\EquipmentService;
use App\Models\Location;
use App\Models\ServiceRequest;
use App\Models\SoftwareRequest;
use App\Models\User;
use App\Models\Workflow;
use App\Traits\CommonFormRelationships;
use App\Traits\Table;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CRF extends Model
{
    use HasFactory, SoftDeletes, Table, CommonFormRelationships;
    protected $table = "crfs";
    protected $guarded = [];
    protected $casts = [
        'expense_type' => ExpenseTypeEnum::class,
        'expense_nature' => ExpenseNatureEnum::class,
    ];
    public function forDepartment()
    {
        return $this->belongsTo(Department::class, 'for_department');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
    public function equipmentRequests(): HasMany
    {
        return $this->hasMany(EquipmentRequest::class, 'crf_id');
    }
    public function softwareRequests(): HasMany
    {
        return $this->hasMany(SoftwareRequest::class, 'crf_id');
    }
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'crf_id');
    }
}
