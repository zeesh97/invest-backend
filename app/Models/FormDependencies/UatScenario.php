<?php

namespace App\Models\FormDependencies;

use App\Models\Forms\SCRF;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UatScenario extends Model
{
    use HasFactory;
    protected $fillable = ['detail', 'status'];
    protected $table = 'uat_scenarios';


    public function scrf(): BelongsTo
    {
        return $this->belongsTo(SCRF::class);
    }
}
