<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormRoleUser extends Model
{
    use HasFactory;
    protected $table = 'form_role_user';
    protected $fillable = ['form_role_id', 'user_id'];
    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formRole(): BelongsTo
    {
        return $this->belongsTo(FormRole::class);
    }

}
