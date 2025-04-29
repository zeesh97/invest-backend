<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class WorkflowInitiatorField extends Model
{
    public $table = "workflow_initiator_fields";

    public $timestamps = false;
    use HasFactory;
    protected $fillable = [
        'workflow_id',
        'form_id',
        'form_key',
        'initiator_id',
        'key_one',
        'key_two',
        'key_three',
        'key_four',
        'key_five',
        'initiator_field_one_id',
        'initiator_field_two_id',
        'initiator_field_three_id',
        'initiator_field_four_id',
        'initiator_field_five_id',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function workflowInitiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id', 'id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id', 'id');
    }
    // public function keyOne()
    // {
    //     $relation = $this->hasOneThrough(SetupField::class, Form::class, 'initiator_field_one_id', 'id', 'key_one');
    //     dd($relation->get());
    //     return (isset($relation) && !is_null($relation)) ? ($relation->first()->identity::find($this->key_one)) : $relation;
    // }
    public function keyOne(): BelongsTo
    {
        $modelClass = $this->getKeyModelClass('initiator_field_one');
        return $this->belongsTo($modelClass, 'key_one')->withDefault(fn () => new $modelClass);
    }

    public function keyTwo(): BelongsTo
    {
        $modelClass = $this->getKeyModelClass('initiator_field_two');
        return $this->belongsTo($modelClass, 'key_two')->withDefault(fn () => new $modelClass);
    }

    public function keyThree(): BelongsTo
    {
        $modelClass = $this->getKeyModelClass('initiator_field_three');
        return $this->belongsTo($modelClass, 'key_three')->withDefault(fn () => new $modelClass);
    }

    public function keyFour(): BelongsTo
    {
        $modelClass = $this->getKeyModelClass('initiator_field_four');
        return $this->belongsTo($modelClass, 'key_four')->withDefault(fn () => new $modelClass);
    }

    public function keyFive(): BelongsTo
    {
        $modelClass = $this->getKeyModelClass('initiator_field_five');
        return $this->belongsTo($modelClass, 'key_five')->withDefault(fn () => new $modelClass);
    }

    protected function getKeyModelClass(string $relation): string
    {
        if (!$this->relationLoaded($relation)) {
            $this->load($relation);
        }

        $setupField = $this->{$relation};

        if (!$setupField) {
            return Department::class; // Default fallback model
        }

        return class_exists($setupField->identity)
            ? $setupField->identity
            : Department::class;
    }

    // public function keyOne()
    // {
    //     $relation = $this->hasOneThrough(SetupField::class, Form::class, 'initiator_field_one_id', 'id', 'key_one');
    //     return isset($relation) ? ($relation->first()->identity::find($this->key_one)) : null;
    // }

    // public function keyTwo()
    // {
    //     $relation = $this->hasOneThrough(SetupField::class, Form::class, 'initiator_field_two_id', 'id', 'key_two');
    //     return isset($relation) ? ($relation->first()->identity::find($this->key_two)) : null;
    // }
    // public function keyThree()
    // {
    //     return $this->hasOneThrough(SetupField::class, Form::class, 'initiator_field_three_id', 'id', 'key_three');
    //     // return isset($relation) ? ($relation->first()->identity::find($this->key_three)) : null;
    // }
    // public function keyFour()
    // {
    //     $relation = $this->hasOneThrough(SetupField::class, Form::class, 'initiator_field_four_id', 'id', 'key_four');
    //     return isset($relation) ? ($relation->first()->identity::find($this->key_four)) : null;
    // }
    // public function keyFive()
    // {
    //     $relation = $this->hasOneThrough(SetupField::class, Form::class, 'initiator_field_five_id', 'id', 'key_five');
    //     return isset($relation) ? ($relation->first()->identity::find($this->key_five)) : null;
    // }

    public function initiator_field_one(): BelongsTo
    {
        return $this->belongsTo(SetupField::class, 'initiator_field_one_id', 'id');
    }
    public function initiator_field_two(): BelongsTo
    {
        return $this->belongsTo(SetupField::class, 'initiator_field_two_id', 'id');
    }
    public function initiator_field_three(): BelongsTo
    {
        return $this->belongsTo(SetupField::class, 'initiator_field_three_id', 'id');
    }
    public function initiator_field_four(): BelongsTo
    {
        return $this->belongsTo(SetupField::class, 'initiator_field_four_id', 'id');
    }
    public function initiator_field_five(): BelongsTo
    {
        return $this->belongsTo(SetupField::class, 'initiator_field_five_id', 'id');
    }
}
