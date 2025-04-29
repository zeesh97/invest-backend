<?php

namespace App\Models\Forms;

use App\Models\Attachment;
use App\Models\QaAssignment;
use App\Models\User;
use App\Traits\CommonFormRelationships;
use App\Traits\Table;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityAssurance extends Model
{
    use HasFactory, SoftDeletes, Table, CommonFormRelationships;
    protected $table = 'quality_assurances';

    protected $guarded = [];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }


    public function attachables(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'attachable_id', 'id');
    }

    /**
     * Get the qaAssignment that owns the QualityAssurance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function qaAssignment(): BelongsTo
    {
        return $this->belongsTo(QaAssignment::class, 'qa_assignment_id', 'id')
        ->with('assurable:id,sequence_no,request_title,task_status', 'assurable.taskStatusName:id,name');
    }
}
