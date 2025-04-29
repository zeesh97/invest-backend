<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserAccessLevel extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['user_id','accessible_type', 'accessible_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function accessible(): MorphTo
    {
        return $this->morphTo();
    }
}
