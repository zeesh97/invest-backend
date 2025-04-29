<?php

namespace App\Traits;
use App\Enums\FormEnum;
use App\Models\Form;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

trait Table
{
    use LogsActivity;
    public function getModelName()
    {
        return $this->getTable();
    }

    public function getModelId(): int
    {
       return FormEnum::getIdByModelName(get_called_class());
    //    return Form::where('identity', get_called_class())->value('id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->dontSubmitEmptyLogs()
        ->useLogName(config(config('app.name') . ' log'));
    }
}
