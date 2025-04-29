<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        // 'company_id',
        'package_id',
        'start_date',
        'end_date',
        'price',
        'number_of_transactions',
        'data_mb',
        'total_users',
        'login_users',
        'transaction_id',
    ];
}
