<?php

namespace App\Http\Requests;

use App\Models\Form;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Validator;

class QualityAssuranceRequest extends FormRequest
{
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $allowedValues = [
            'Testing Result Ok',
            'Testing Not Successful',
            'Not Relevant',
        ];
        $values = implode(', ', $allowedValues);
        $rules = [
            'form_id' => [
                'required',
                Rule::exists('forms', 'id')->where(function ($query) {
                    $query->where('id', '!=', 3);
                })
            ],
            'key' => [
                'required',
                'integer',
                $this->validateFormKeyExists(),
            ],
            'status' => 'required|in:' . ucwords('Testing Ok') . ',' . ucwords('Modification Required'),

            'feedback' => 'nullable|string|min:2|max:1000',
            'save_as_draft' => 'required|string',
            'location_id' => 'required|exists:locations,id',
            'assigned_to_ids' => 'required|array',
            'assigned_to_ids*' => 'integer|exists:users,id',
        ];

        //For custom validation array_length
        Validator::extend('array_length', function ($attribute, $value, $parameters, $validator) {
            return count($value) === (int) $parameters[0];
        });

        Validator::replacer('array_length', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':count', $parameters[0], 'The :attribute must contain exactly :count UAT scenario results.');
        });

        if ($this->form_id == 2) {
            // dd($allowedValues);
            $rules['uat_options'] = 'required|array|array_length:'.$this->getData()->uatScenarios->count();
            // $rules['uat_options.*'] = 'required|in:Testing Result Ok, Testing Not Successful, Not Relevant';
        }

        return $rules;
    }

    protected function validateFormKeyExists()
    {
        $table = $this->getData()->getTable();
        return Rule::exists($table, 'id');
    }

    public function getData()
    {
        $record = $this->formKeyExists()->identity::findOrFail($this->key);
        return $record;
    }

    protected function formKeyExists()
    {
        return Form::findOrFail($this->form_id);
    }
}
