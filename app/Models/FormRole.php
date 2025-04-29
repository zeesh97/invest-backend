<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormRole extends Model
{
    use HasFactory;

    public function formPermissions()
    {
        return $this->hasMany(FormPermissionable::class);
    }
    public function departments()
    {
        return $this->morphedbyMany(Department::class, 'form_permissionable');
    }
    public function locations()
    {
        return $this->morphedbyMany(Location::class, 'form_permissionable');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'form_role_user');
    }
    public function hasFormPermission($permission)
    {
        return $this->formPermissions->contains('name', $permission);
    }
}
