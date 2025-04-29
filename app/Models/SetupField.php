<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class SetupField extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'identity'];

    public function getRelatedModel()
    {
        if (class_exists($this->identity)) {
            return new $this->identity;
        }

        return null;
    }

    /**
     * Get all of the formKeys for the SetupField
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function formKeys(): HasManyThrough
    {
        return $this->hasManyThrough(WorkflowInitiatorField::class, Form::class, 'initiator_field_one_id');
    }
}
