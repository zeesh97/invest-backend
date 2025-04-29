<?php

namespace App\Http\Requests;

use App\Models\Form;
use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Validator;

class StoreQualityAssuranceRequest extends FormRequest
{
    private $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }
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
        $allowedExtensionsJson = $this->settingService->getAllowedExtensions();
        $allowedExtensions = json_decode($allowedExtensionsJson, true);

        if (!is_array($allowedExtensions)) {
            $allowedExtensions = [];
        }

        $allowedExtensionsString = implode(',', $allowedExtensions);
        $allowedValues = [
            'Testing Result Ok',
            'Testing Not Successful',
            'Not Relevant',
        ];
        $values = implode(', ', $allowedValues);
        $allowedExtensionsJson = $this->settingService->getAllowedExtensions();
        $allowedExtensions = json_decode($allowedExtensionsJson, true);

        if (!is_array($allowedExtensions)) {
            $allowedExtensions = [];
        }

        $allowedExtensionsString = implode(',', $allowedExtensions);
        $rules = [
            'save_as_draft' => 'required|in:true,false',
            // 'request_title' => 'required|string|max:255',

        ];

        $reqOrOpt = 'required';
        if ($this->input('save_as_draft') === 'true') {
            $reqOrOpt = 'nullable';
        }
        // dd($this->input('save_as_draft'));
        $rules = array_merge($rules, [
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
            // 'assigned_to_ids' => 'required|array',
            // 'assigned_to_ids*' => 'integer|exists:users,id',
            // 'attachments' => 'nullable|array',
            // 'attachments.*' => 'nullable|mimes:' . $allowedExtensionsString . '|max:' . $this->settingService->getMaxUploadSize(),
        ]);


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
