<?php

namespace App\Models;

use App\Models\Forms\MasterDataManagementForm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class MdmCategory extends Model
{
    use HasFactory;

    protected $table = 'mdm_categories';
    protected $fillable = ['name'];

    public function masterDataManagementForms(): HasMany
    {
        return $this->hasMany(MasterDataManagementForm::class);
    }
}
