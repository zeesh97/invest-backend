<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'crf_id',
        'name',
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
}
