<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ParallelApprover extends Pivot
{
    use HasFactory;
    protected $table = 'approver_location_parallel_user';
    protected $fillable = [
        'user_id',
        'parallel_user_id',
        'location_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parallelUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parallel_user_id');
    }

    // public function location(): BelongsTo
    // {
    //     return $this->belongsTo(Location::class);
    // }
}
