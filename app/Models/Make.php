<?php

namespace App\Models;

use App\Models\Forms\SCRF;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Make extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'makes';
    protected $fillable = ['name'];

    // public function form_permissions(): MorphMany
    // {
    //     return $this->morphMany(FormPermissionable::class, 'form_permissionable');
    // }
    public function form_permissions(): MorphToMany
    {
        return $this->morphToMany(FormRole::class, 'form_permissionable');
    }
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scrfs(): HasMany
    {
        return $this->hasMany(SCRF::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
