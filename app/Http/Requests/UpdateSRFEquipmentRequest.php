<?php

namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSRFEquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $modelName = preg_match('/Update(.*)Request/', get_class(), $matches) ? $matches[1] : null;
        $modelName = 'App\\Models\\Forms\\'.$modelName;
        $instance = new $modelName;
        $tableName = $instance->getTable();
        $record = $modelName::findOrFail($this->route($tableName));
        return (Auth::user()->id == $record->created_by && $record->status !== 'Approved' && $record->status !== 'Pending') ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'request_title' => 'required|max:250',

            'equipment_requests' => 'nullable|array',
            'equipment_requests.*.equipment_id' => 'nullable|exists:equipment,id',
            'equipment_requests.*.quantity' => 'nullable|numeric|min:1|max:9999999',
            'equipment_requests.*.state' => 'nullable|in:1,3,4,5,6',
            'equipment_requests.*.expense_type' => 'nullable|in:1,2',
            'equipment_requests.*.expense_nature' => 'nullable|in:1,2',
            'equipment_requests.*.business_justification' => 'nullable|string|min:3|max:1000',
            'equipment_requests.*.amount' => 'nullable|numeric|min:0.01|max:1000000000',
            'equipment_requests.*.currency' => 'nullable|string|min:3|max:1000|regex:/^[A-Za-z]{3}$/',
            'equipment_requests.*.rate' => 'nullable|numeric|min:0.01|max:1000000000',

            // 'equipment_requests.*.asset_details' => 'nullable|array',
            'equipment_requests.*.asset_details' => 'nullable|json',
            'equipment_requests.*.asset_details.*.action' => 'nullable|in:Issued from Inventory,Internal Transfer,Purchase',
            'equipment_requests.*.asset_details.*.request_type' => 'nullable|in:equipment_request',
            'equipment_requests.*.asset_details.*.inventory_status' => 'nullable|string',
            'equipment_requests.*.asset_details.*.expected_expense' => 'nullable|string',
            'equipment_requests.*.asset_details.*.serial_no' => 'nullable|string',
            'equipment_requests.*.asset_details.*.asset_code' => 'nullable|string',
            'equipment_requests.*.asset_details.*.description' => 'nullable|string',
            'equipment_requests.*.asset_details.*.remarks' => 'nullable|string',


            'software_requests' => 'nullable|array',
            'software_requests.*.software_name' => 'nullable|max:250',
            'software_requests.*.quantity' => 'nullable|numeric|min:1|max:9999999',
            'software_requests.*.version' => 'nullable|min:1|max:100',
            'software_requests.*.expense_type' => 'nullable|in:1,2',
            'software_requests.*.expense_nature' => 'nullable|in:1,2',
            'software_requests.*.business_justification' => 'nullable|string|min:3|max:1000',
            'software_requests.*.amount' => 'nullable|numeric|min:0.01|max:1000000000',
            'software_requests.*.currency' => 'nullable|string|min:3|max:1000|regex:/^[A-Za-z]{3}$/',
            'software_requests.*.rate' => 'nullable|numeric|min:0.01|max:1000000000',

            // 'software_requests.*.asset_details' => 'nullable|array',
            'software_requests.*.asset_details' => 'nullable|json',
            'software_requests.*.asset_details.*.action' => 'nullable|in:Issued from Inventory,Internal Transfer,Purchase',
            'software_requests.*.asset_details.*.request_type' => 'nullable|in:equipment_request,service_request,software_request',
            'software_requests.*.asset_details.*.inventory_status' => 'nullable|string',
            'software_requests.*.asset_details.*.expected_expense' => 'nullable|string',
            'software_requests.*.asset_details.*.serial_no' => 'nullable|string',
            'software_requests.*.asset_details.*.asset_code' => 'nullable|string',
            'software_requests.*.asset_details.*.description' => 'nullable|string',
            'software_requests.*.asset_details.*.remarks' => 'nullable|string',


            'service_requests' => 'nullable|array',
            'service_requests.*.service_name' => 'nullable|max:250',
            'service_requests.*.state' => 'nullable|in:1,2',
            'service_requests.*.expense_type' => 'nullable|in:1,2',
            'service_requests.*.expense_nature' => 'nullable|in:1,2',
            'service_requests.*.business_justification' => 'nullable|string|min:3|max:1000',
            'service_requests.*.amount' => 'nullable|numeric|min:0.01|max:1000000000',
            'service_requests.*.currency' => 'nullable|string|min:3|max:1000|regex:/^[A-Za-z]{3}$/',
            'service_requests.*.rate' => 'nullable|numeric|min:0.01|max:1000000000',

            // 'service_requests.*.asset_details' => 'nullable|array',
            'service_requests.*.asset_details' => 'nullable|json',
            'service_requests.*.asset_details.*.action' => 'nullable|in:Issued from Inventory,Internal Transfer,Purchase',
            'service_requests.*.asset_details.*.request_type' => 'nullable|in:equipment_request,service_request,software_request',
            'service_requests.*.asset_details.*.inventory_status' => 'nullable|string',
            'service_requests.*.asset_details.*.expected_expense' => 'nullable|string',
            'service_requests.*.asset_details.*.serial_no' => 'nullable|string',
            'service_requests.*.asset_details.*.asset_code' => 'nullable|string',
            'service_requests.*.asset_details.*.description' => 'nullable|string',
            'service_requests.*.asset_details.*.remarks' => 'nullable|string',

            // 'asset_details.*.action' => 'nullable|in:Issued from Inventory,Internal Transfer,Purchase',
            // 'asset_details.*.request_type' => 'nullable|in:equipment_request,service_request,software_request',
            // 'asset_details.*.inventory_status' => 'nullable|string',
            // 'asset_details.*.expected_expense' => 'nullable|string',
            // 'asset_details.*.serial_no' => 'nullable|string',
            // 'asset_details.*.asset_code' => 'nullable|string',
            // 'asset_details.*.description' => 'nullable|string',
            // 'asset_details.*.remarks' => 'nullable|string',

            'save_as_draft' => 'in:true,false',
        ];
    }
}
