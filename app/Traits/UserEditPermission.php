<?php

namespace App\Traits;

use App\Enums\FormEnum;
use App\Models\ApprovalStatus;
use Illuminate\Support\Facades\Auth;
use Route;

trait UserEditPermission
{
    protected function ifInitiatedByMe($class): bool
    {
        $modelName = $this->getModelName($class);

        $record = $modelName::findOrFail($this->getCurrentRouteId());
        $formId = FormEnum::getIdByModelName($modelName);
        return ((Auth::user()->id == $record->created_by && $record->status !== 'Approved' && $record->status !== 'Pending') || $this->ifAuthorized($formId, $record->id)) ? true : false;
    }

    protected function ifAuthorized($formId, $recordId): bool
    {
        return ApprovalStatus::where('form_id', $formId)
            ->where('key', $recordId)
            ->where('user_id', Auth::user()->id)->exists();
    }

    protected function getModelName(mixed $class): ?string
    {
        if (!is_string($class) || !class_exists($class)) {
            return null; // Handle invalid input
        }

        if (!preg_match('/Update(.*)Request/', $class, $matches)) {
            return null;  // Pattern doesn't match
        }

        $modelName = $matches[1];
        $fullyQualifiedModelName = "App\\Models\\Forms\\" . $modelName;

        if (class_exists($fullyQualifiedModelName)) {
            return $fullyQualifiedModelName;
        }

        return null;
    }

    protected function getCurrentRouteId()
    {
        $route = Route::current();
        $parameters = $route->parameters();
        $parameterValues = array_values($parameters);
        return $parameterValues[0];
    }
}
