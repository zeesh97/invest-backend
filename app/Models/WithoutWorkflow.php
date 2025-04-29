<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithoutWorkflow extends Model
{
    protected $table = "without_workflows";
    protected $fillable = [
        'form_id',
        'software_category_id',
        'created_by',
        'updated_by',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function softwareCategory(): BelongsTo
    {
        return $this->belongsTo(SoftwareCategory::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


}
