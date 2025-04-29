<?php

namespace App\Models;

use App\Http\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;
    protected $fillable = ['attachable_id', 'attachable_type', 'filename', 'original_title'];

    public function getCreatedAtAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        $timezone = Helper::appTimezone();
        return Carbon::parse($value)->timezone($timezone)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        $timezone = Helper::appTimezone();
        return Carbon::parse($value)->timezone($timezone)->format('d-m-Y H:i:s');
    }
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
