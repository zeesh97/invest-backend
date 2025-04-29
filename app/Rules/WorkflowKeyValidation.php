<?php

namespace App\Rules;

use App\Models\Form;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WorkflowKeyValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $formId = request()->input('form_id');
        $key = substr($attribute, strlen('key_'));

        $initiatorField = 'initiator_field_'.$key.'_id';

        $form = Form::find($formId);

        if ($form && is_null($form->$initiatorField)) {
            $fail("The initiator field corresponding to $attribute is not defined or null in the form.");
          }
    }
}
