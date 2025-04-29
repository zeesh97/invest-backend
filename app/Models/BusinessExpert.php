<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessExpert extends Model
{
    use HasFactory;
    protected $fillable = ['software_subcategory_id', 'business_expert_user_id'];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_expert_user_id', 'id');
    }

    public function software_subcategory(): BelongsTo
    {
        return $this->belongsTo(SoftwareSubcategory::class);
    }
}
