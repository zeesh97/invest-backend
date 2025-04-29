<?php

namespace App\Models\Forms;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\CostCenter;
use App\Models\EquipmentRequest;
use App\Models\EquipmentService;
use App\Models\Location;
use App\Models\ServiceRequest;
use App\Models\SoftwareRequest;
use App\Models\User;
use App\Models\Workflow;
use App\Traits\CommonFormRelationships;
use App\Traits\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SRFEquipment extends Model
{
    use HasFactory, SoftDeletes, Table, CommonFormRelationships;
    protected $table = "srf_equipments";
    protected $guarded = [];
    protected $casts = [
        'expense_type' => ExpenseTypeEnum::class,
        'expense_nature' => ExpenseNatureEnum::class
    ];

    public function attachables(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'attachable_id', 'id');
    }
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
    public function equipmentRequests(): HasMany
    {
        return $this->hasMany(EquipmentRequest::class, 'srf_equipment_id');
    }
    public function softwareRequests(): HasMany
    {
        return $this->hasMany(SoftwareRequest::class, 'srf_equipment_id');
    }
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'srf_equipment_id');
    }
    // public function comments(): HasMany
    // {
    //     return $this->hasMany(Comment::class, 'srf_equipment_id', 'id')->whereNull('parent_id');
    // }



}
