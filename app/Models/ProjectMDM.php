<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMDM extends Model
{
    use HasFactory;
    protected $table = 'project_mdm_software_categories';

    public $fillable = ['mdm_category_id', 'name', 'software_category_id'];

    public function mdmCategory(): BelongsTo
    {
        return $this->belongsTo(MdmCategory::class, 'mdm_category_id');
    }

    public function softwareCategory(): BelongsTo
    {
        return $this->belongsTo(SoftwareCategory::class);
    }
}
