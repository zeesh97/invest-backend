<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoftwareCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function software_subcategories(): HasMany
    {
        return $this->hasMany(SoftwareSubcategory::class);
    }
}
