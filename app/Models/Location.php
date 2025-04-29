<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name'];

    public function form_permissions(): MorphToMany
    {
        return $this->morphToMany(FormRole::class, 'form_permissionable');
    }
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function parallelApproverUsers()
    {
        return $this->belongsToMany(User::class, 'parallel_approver_user_location', 'location_id', 'user_id')
            ->withPivot('parallel_user_id')
            ->withTimestamps();
    }
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    // public function parallelApprovers()
    // {
    //     return $this->belongsToMany(ParallelApprover::class, 'parallel_approver_user_location', 'location_id', 'parallel_user_id')
    //         ->withPivot('user_id')
    //         ->withTimestamps();
    // }
}
