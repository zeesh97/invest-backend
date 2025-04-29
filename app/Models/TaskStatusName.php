<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskStatusName extends Model
{
    use HasFactory;
    public $table = 'task_status_names';
    // protected $fillable = [
    //     'name'
    // ];
    public $timestamps = false;
}
