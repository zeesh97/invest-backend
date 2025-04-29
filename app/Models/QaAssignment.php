<?php

namespace App\Models;

use App\Models\Forms\QualityAssurance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QaAssignment extends Model
{
    use HasFactory;
    // protected $table = 'qa_assignments';

    protected $fillable = [
        'qa_user_id',
        'assurable_type',
        'assurable_id',
        'status',
        'status_at',
        'assigned_by',
        'feedback',
    ];
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function qaUser()
    {
        return $this->belongsTo(User::class, 'qa_user_id');
    }

    public function assurable(): MorphTo
    {
        return $this->morphTo();
    }
    public function qualityAssurances(): HasMany
    {
        return $this->hasMany(QualityAssurance::class, 'qa_assignment_id', 'id');
    }
}
