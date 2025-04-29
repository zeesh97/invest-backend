<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'number_of_transactions',
        'data_mb',
        'total_users',
        'login_users',
        'period_type_id',
        'is_active',
    ];


}
