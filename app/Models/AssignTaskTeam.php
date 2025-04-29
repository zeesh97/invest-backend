<?php

namespace App\Models;

use App\Http\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignTaskTeam extends Model
{
    use HasFactory;
    protected $table = 'assign_task_team';
    public $timestamps = false;
    protected $guarded = [];

    // public function getCreatedAtAttribute($value): ?Carbon
    // {
    //     if (!$value) {
    //         return null;
    //     }

    //     $timezone = Helper::appTimezone();
    //     return Carbon::parse($value)->timezone($timezone);
    // }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
