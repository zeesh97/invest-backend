<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCRFRequest extends FormRequest
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
        $rules = [
            'save_as_draft' => 'required|in:true,false',
            'request_title' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'department_id' => 'required|exists:departments,id',
            'for_department' => 'nullable|exists:departments,id',
            'for_employee' => 'nullable|string|max:150',
        ];

        $reqOrOpt = 'required';
        if ($this->input('save_as_draft') === 'true') {
            $reqOrOpt = 'nullable';
        }
        $rules = array_merge($rules, [
            // 'request_specs' => $reqOrOpt . '|string|max:10000',
            'cost_center_id' => $reqOrOpt . '|numeric|exists:cost_centers,id',
            'equipment_requests' => Rule::requiredIf(function () {
                return empty(request()->input('software_requests')) && empty(request()->input('service_requests'));
            }, 'array'),
            'equipment_requests.*.equipment_id' => 'nullable|exists:equipment,id',
            'equipment_requests.*.quantity' => 'nullable|numeric|min:1|max:9999999',
            'equipment_requests.*.state' => 'nullable|in:1,3,4,5,6',
            'equipment_requests.*.expense_type' => 'nullable|in:1,2',
            'equipment_requests.*.expense_nature' => 'nullable|in:1,2',
            'equipment_requests.*.business_justification' => 'nullable|string|min:3|max:1000',

            'software_requests' => Rule::requiredIf(function () {
                return empty(request()->input('equipment_requests')) && empty(request()->input('service_requests'));
            }, 'array'),
            'software_requests.*.software_name' => 'nullable|max:250',
            'software_requests.*.quantity' => 'nullable|numeric|min:1|max:9999999',
            'software_requests.*.version' => 'nullable|min:1|max:100',
            'software_requests.*.expense_type' => 'nullable|in:1,2',
            'software_requests.*.expense_nature' => 'nullable|in:1,2',
            'software_requests.*.business_justification' => 'nullable|string|min:3|max:1000',

            'service_requests' => Rule::requiredIf(function () {
                return empty(request()->input('equipment_requests')) && empty(request()->input('software_requests'));
            }, 'array'),
            'service_requests.*.service_name' => 'nullable|max:250',
            'service_requests.*.state' => 'nullable|in:1,2',
            'service_requests.*.expense_type' => 'nullable|in:1,2',
            'service_requests.*.expense_nature' => 'nullable|in:1,2',
            'service_requests.*.business_justification' => 'nullable|string|min:3|max:1000',
        ]);

        return $rules;
    }
}
