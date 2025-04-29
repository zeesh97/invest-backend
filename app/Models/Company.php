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

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'companies';
    protected $fillable = [
        'logo',
        'code',
        'name',
        'long_name',
        'ntn_no',
        'sales_tax_no',
        'postal_code',
        'address',
        'phone'
    ];

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
