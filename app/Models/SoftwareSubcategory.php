<?php

namespace App\Models;

use App\Models\Forms\SCRF;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoftwareSubcategory extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'software_category_id'];


    public function software_category(): BelongsTo
    {
        return $this->belongsTo(SoftwareCategory::class);
    }

    public function scrfs()
    {
        return $this->belongsToMany(SCRF::class, 'scrf_software_subcategory', 'software_subcategory_id', 'scrf_id');
    }
}
