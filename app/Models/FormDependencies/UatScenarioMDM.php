<?php

namespace App\Models\FormDependencies;

use App\Models\Forms\MasterDataManagementForm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UatScenarioMDM extends Model
{
    use HasFactory;
    protected $fillable = ['detail', 'status'];
    protected $table = 'uat_scenarios_mdm';


    public function masterDataManagementForm(): BelongsTo
    {
        return $this->belongsTo(MasterDataManagementForm::class);
    }
}
