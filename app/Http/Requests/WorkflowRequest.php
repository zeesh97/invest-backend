<?php

namespace App\Http\Requests;

use App\Http\Helpers\Helper;
use App\Models\Approver;
use App\Models\Form;
use App\Models\SetupField;
use App\Models\Subscriber;
use App\Rules\WorkflowKeyValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkflowRequest extends FormRequest
{
    protected $formId;
    protected $initiatorFieldOne;
    protected $initiatorFieldTwo;
    protected $initiatorFieldThree;
    protected $initiatorFieldFour;
    protected $initiatorFieldFive;
    protected $workflowId;

    // public function validate_initiator_field($attribute, $value, $fail)
    // {
    //     // $form = Form::where('id', $value)
    //     //     ->where(function ($query) {
    //     //         $query->whereNotNull('initiator_field_one_id')
    //     //             ->orWhereNotNull('initiator_field_two_id')
    //     //             ->orWhereNotNull('initiator_field_three_id')
    //     //             ->orWhereNotNull('initiator_field_four_id')
    //     //             ->orWhereNotNull('initiator_field_five_id');
    //     //     })
    //     //     ->first();
    //     // $form = Form::where('id', $value)
    //     //     ->whereNotNull('initiator_field_one_id')
    //     //     ->whereNotNull('initiator_field_two_id')
    //     //     ->whereNotNull('initiator_field_three_id')
    //     //     ->whereNotNull('initiator_field_four_id')
    //     //     ->whereNotNull('initiator_field_five_id')
    //     //     ->take(1)
    //     //     ->first();

    //     $form = Form::find($value);
    //     if ($form) {
    //         $form = $form->where(function ($query) {
    //             $query->where('initiator_field_one_id', '!=', null)
    //                 ->orWhere('initiator_field_two_id', '!=', null)
    //                 ->orWhere('initiator_field_three_id', '!=', null)
    //                 ->orWhere('initiator_field_four_id', '!=', null)
    //                 ->orWhere('initiator_field_five_id', '!=', null);
    //         })->first();
    //     }

    //     if (!is_null($form)) {
    //         $setupFields = SetupField::all(['id', 'name', 'identity']);
    //         $initiatorFields = [
    //             'initiator_field_one_id',
    //             'initiator_field_two_id',
    //             'initiator_field_three_id',
    //             'initiator_field_four_id',
    //             'initiator_field_five_id',
    //         ];
    //         $matchedIdentities = [];

    //         foreach ($initiatorFields as $field) {
    //             if (!is_null($form->$field)) {
    //                 $matchedSetupField = $setupFields->firstWhere('id', $form->$field);
    //                 if ($matchedSetupField) {
    //                     try {
    //                         $matchedIdentities[] = $matchedSetupField->identity::where('id', $attribute)->exists();

    //                         if (!$matchedIdentities) {
    //                             $fail('The values are incorrect or already exists.');
    //                         }
    //                     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //                         // Handle the exception if necessary
    //                     }
    //                 }
    //             }
    //         }
    //     } else {
    //         return Helper::sendError("Please define Form Initiator Fields for this Form first.", [], 422);
    //     }
    // }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        // dd($this->request->all());
        $rules = [
            'form_id' => [
                'required',
                'exists:forms,id',
                function ($attribute, $value, $fail) {
                    $form = Form::findOrFail($value);

                    $initiatorFields = [
                        'initiator_field_one_id',
                        'initiator_field_two_id',
                        'initiator_field_three_id',
                        'initiator_field_four_id',
                        'initiator_field_five_id',
                    ];

                    if ($this->allFieldsNull($form, $initiatorFields)) {
                        $fail('The specified Form is invalid or does not have initiator fields defined in Forms.');
                    }
                },
            ],

            'initiator_id' => [
                'required',
                'exists:users,id',
                Rule::unique('workflow_initiator_fields')
                    ->where(function ($query) {
                        return $query
                            ->where('key_one', $this->input('key_one'))
                            ->where('key_two', $this->input('key_two'))
                            ->where('key_three', $this->input('key_three'))
                            ->where('key_four', $this->input('key_four'))
                            ->where('key_five', $this->input('key_five'))
                            ->where('form_id', $this->input('form_id'));
                    })
                    ->ignore($this->route('workflow_initiator_field')),
            ],

            'key_one' => ['nullable', 'integer', new WorkflowKeyValidation()],
            'key_two' => ['nullable', 'integer', new WorkflowKeyValidation()],
            'key_three' => ['nullable', 'integer', new WorkflowKeyValidation()],
            'key_four' => ['nullable', 'integer', new WorkflowKeyValidation()],
            'key_five' => ['nullable', 'integer', new WorkflowKeyValidation()],

            'callback_id' => ['nullable', 'exists:callbacks,id']
        ];

        $rules['workflowSubscribersApprovers'] = 'required|array|min:1';

        foreach ($this->input('workflowSubscribersApprovers', []) as $index => $subscriberApprover) {
            $rules['workflowSubscribersApprovers.' . $index . '.approver_id'] = [
                'required',
                'exists:approvers,id',
            ];
            $rules['workflowSubscribersApprovers.' . $index . '.subscriber_id'] = [
                'nullable',
                'exists:subscribers,id',
            ];
            $rules['workflowSubscribersApprovers.' . $index . '.approval_condition'] = ['nullable', 'exists:conditions,id'];
            $rules['workflowSubscribersApprovers.' . $index . '.sequence_no'] = [
                'required',
                'integer',
                'min:1',
            ];
            $rules['workflowSubscribersApprovers.' . $index . '.editable'] = ['boolean'];
        }

        // dd($rules);
        return $rules;
    }

    public function messages(): array
    {
        return [
            'initiator_id.unique' => 'The workflow for this form and user is already defined.',
            'initiator_id.exists' => 'Entry already exists.',
        ];
    }

    private function allFieldsNull($form, $fields)
    {
        return collect($fields)->every(function ($field) use ($form) {
            return is_null($form->$field);
        });
    }
}
