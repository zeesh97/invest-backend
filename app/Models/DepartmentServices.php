<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentServices extends Model
{
    use HasFactory;
    protected $table = 'department_service';
    protected $fillable = ['department_id', 'service_id'];
}
