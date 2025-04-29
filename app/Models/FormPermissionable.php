<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormPermissionable extends Model
{
    use HasFactory;
    protected $fillable = ['form_role_id', 'form_id', 'form_permissionable_type', 'form_permissionable_id'];

    public function formRole()
    {
        return $this->belongsTo(FormRole::class, 'form_role_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function formPermissionable()
    {
        return $this->morphTo();
    }
}
