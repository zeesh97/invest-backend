<?php

namespace App\Rules;

use App\Models\Form;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FormKeyExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $formId = request()->input('form_id');

        $formClass = Form::findOrFail($formId)->identity;
        if (!$formClass::where('id', $value)->exists()) {
            $fail('The combination of form_id and key is invalid.');
        }
    }


    public function message()
    {
        return 'The combination of form_id and key is invalid.';
    }
}
