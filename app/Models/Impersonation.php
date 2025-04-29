<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Impersonation extends Model
{
    protected $table = 'impersonations';
    protected $fillable = [
        'admin_id',
        'impersonated_id',
        'token',
        'ip_address',
        'user_agent',
        'expires_at'
    ];
    protected $casts = [
        'expires_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    // Scope for active impersonations
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now())
                    ->whereNull('ended_at');
    }

    // Relationships
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function impersonatedUser()
    {
        return $this->belongsTo(User::class, 'impersonated_id');
    }
}
