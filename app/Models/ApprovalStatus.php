<?php

namespace App\Models;

use App\Http\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStatus extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'workflow_id',
    //     'approver_id',
    //     'user_id',
    //     'approval_required',
    //     'sequence_no',
    //     'form_id',
    //     'key',
    //     'reason',
    //     'editable',
    //     'status',
    //     'responded_by'
    // ];
    protected $guarded = ['created_at', 'updated_at'];
    protected $casts = [
        'editable' => 'boolean',
        'is_parallel' => 'boolean'
    ];
    public $timestamps = false;
    public function getStatusAtAttribute($value): ?String
    {
        if (!$value) {
            return null;
        }

        $timezone = Helper::appTimezone();
        return Carbon::parse($value)->timezone($timezone);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Approver::class);
    }
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class, 'subscriber_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by', 'id');
    }
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
