<?php

namespace App\Models;

use App\Enums\StateEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'crf_id',
        'equipment_id',
        'qty',
        'state',
        'expense_type',
        'expense_nature',
        'business_justification',
        'amount',
        'currency',
        'currency_default',
        'rate',
        'total',
        'asset_details',
    ];

    protected $casts = [
        'asset_details' => 'array',
    ];

    // public function getStateAttribute($value)
    // {
    //     // dd($value);
    //     return StateEnum::fromValue($value)->name;
    // }
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
