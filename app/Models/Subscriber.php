<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscriber extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'subscriber_user', 'subscriber_id', 'user_id');
    }

    public function workflows(): BelongsToMany
    {
        return $this->belongsToMany(Workflow::class, 'workflow_subscribers_approvers', 'subscriber_id', 'workflow_id');
    }

}
