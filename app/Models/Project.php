<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $table = "projects";
    protected $fillable = [
        'name',
        'description',
        'form_id',
        'created_by',
        'updated_by',
    ];
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
