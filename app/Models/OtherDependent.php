<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherDependent extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'data'];

    protected $casts = [
        'data' => 'array',
    ];
}
