<?php

namespace App\Models\FormDependencies;

use App\Models\Forms\Deployment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentDetail extends Model
{
    use HasFactory;
    protected $fillable = ['detail', 'document_no'];
    protected $table = 'document_details_deployment';

    public function deploymentForm(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }
}
