<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSRFEquipmentRequest extends FormRequest
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
        return [
            'location_id' => 'required|exists:locations,id',
            'request_title' => 'required|max:250',

            'equipment_requests' => 'nullable|array',
            'equipment_requests.*.equipment_id' => 'nullable|exists:equipment,id',
            'equipment_requests.*.quantity' => 'nullable|numeric|min:1|max:9999999',
            'equipment_requests.*.state' => 'nullable|in:1,3,4,5,6',
            'equipment_requests.*.expense_type' => 'nullable|in:1,2',
            'equipment_requests.*.expense_nature' => 'nullable|in:1,2',
            'equipment_requests.*.business_justification' => 'nullable|string|min:3|max:1000',

            'software_requests' => 'nullable|array',
            'software_requests.*.software_name' => 'nullable|max:250',
            'software_requests.*.quantity' => 'nullable|numeric|min:1|max:9999999',
            'software_requests.*.version' => 'nullable|min:1|max:100',
            'software_requests.*.expense_type' => 'nullable|in:1,2',
            'software_requests.*.expense_nature' => 'nullable|in:1,2',
            'software_requests.*.business_justification' => 'nullable|string|min:3|max:1000',

            'service_requests' => 'nullable|array',
            'service_requests.*.service_name' => 'nullable|max:250',
            'service_requests.*.state' => 'nullable|in:1,2',
            'service_requests.*.expense_type' => 'nullable|in:1,2',
            'service_requests.*.expense_nature' => 'nullable|in:1,2',
            'service_requests.*.business_justification' => 'nullable|string|min:3|max:1000',

            'save_as_draft' => 'in:true,false',
        ];
    }
}
