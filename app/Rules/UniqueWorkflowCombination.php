<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\WorkflowInitiatorField;

class UniqueWorkflowCombination implements Rule
{
    public function passes($attribute, $value)
    {
        $formId = $value['form_id'];
        $initiatorId = $value['initiator_id'];
        $keyOne = $value['key_one'];
        $keyTwo = $value['key_two'];
        $keyThree = $value['key_three'];
        $keyFour = $value['key_four'];
        $keyFive = $value['key_five'];

        // Check if a record with the same combination exists in the database
        return !WorkflowInitiatorField::where([
            'form_id' => $formId,
            'initiator_id' => $initiatorId,
            'key_one' => $keyOne,
            'key_two' => $keyTwo,
            'key_three' => $keyThree,
            'key_four' => $keyFour,
            'key_five' => $keyFive,
        ])->exists();
    }

    public function message()
    {
        return 'The combination of values is not unique.';
    }
}
