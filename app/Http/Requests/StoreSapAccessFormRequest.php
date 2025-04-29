<?php

namespace App\Http\Requests;

use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSapAccessFormRequest extends FormRequest
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
        $rules = [
            'save_as_draft' => 'required|in:true,false',

            'location' => 'array',
            'location.*.id' => [
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    if (!\App\Http\Helpers\LocationHelper::findById($value)) {
                        $fail("The selected {$attribute} is invalid.");
                    }
                },
            ],

            'plant' => 'array',
            'plant.*.id' => [
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    if (!\App\Http\Helpers\PlantHelper::findById($value)) {
                        $fail("The selected {$attribute} is invalid.");
                    }
                },
            ],

            'sales_distribution' => 'array',
            'sales_distribution.*.sales_organization' => 'nullable|string|max:10000',
            'sales_distribution.*.distribution_channel' => 'nullable|string|max:10000',
            'sales_distribution.*.division' => 'nullable|string|max:10000',
            'sales_distribution.*.sales_office' => 'nullable|string|max:10000',
            'sales_distribution.*.sales_group' => 'nullable|string|max:10000',
            'sales_distribution.*.other_details' => 'nullable|string|max:10000',

            'material_management' => 'array',
            'material_management.*.purchasing_org' => 'nullable|string|max:10000',
            'material_management.*.purchasing_group' => 'nullable|string|max:10000',
            'material_management.*.storage_location' => 'nullable|string|max:10000',
            'material_management.*.purchasing_document' => 'nullable|string|max:10000',
            'material_management.*.movement_type' => 'nullable|string|max:10000',
            'material_management.*.other_details' => 'nullable|string|max:10000',

            'plant_maintenance' => 'array',
            'plant_maintenance.*.planning_plant' => 'nullable|string|max:10000',
            'plant_maintenance.*.maintenance_plant' => 'nullable|string|max:10000',
            'plant_maintenance.*.work_centers' => 'nullable|string|max:10000',
            'plant_maintenance.*.other_details' => 'nullable|string|max:10000',

            'financial_accounting_costing' => 'array',
            'financial_accounting_costing.*.profile_center' => 'nullable|string|max:10000',
            'financial_accounting_costing.*.cost_center' => 'nullable|string|max:10000',
            'financial_accounting_costing.*.other_details' => 'nullable|string|max:10000',


            'production_planning' => 'array',
            'production_planning.*.other_details' => 'nullable|string|max:10000',


            'human_resource' => 'array',
            'human_resource.*.personnel_area' => 'nullable|string|max:10000',
            'human_resource.*.sub_area' => 'nullable|string|max:10000',
            'human_resource.*.employee_group' => 'nullable|string|max:10000',
            'human_resource.*.employee_sub_group' => 'nullable|string|max:10000',
            'human_resource.*.other_details' => 'nullable|string|max:10000',

        ];

        $reqOrOpt = 'required';
        if ($this->input('save_as_draft') === 'true') {
            $reqOrOpt = 'nullable';
        }
        $rules = array_merge($rules, [
            'request_title' => $reqOrOpt . '|string|max:255',
            'roles_required' => $reqOrOpt . '|string',
            'tcode_required' => $reqOrOpt . '|string',
            'business_justification' => $reqOrOpt . '|string',
            'company_id' => $reqOrOpt . '|exists:companies,id',
            'type' => $reqOrOpt . '|string|in:New,Modification',
            'sap_id' => 'nullable|string',

            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|mimes:' . $allowedExtensionsString . '|max:' . $this->settingService->getMaxUploadSize(),
        ]);


        return $rules;
    }

    // public function withValidator(Validator $validator): void
    // {
    //     $validator->after(function (Validator $validator) {
    //         // Collect all arrays from the request
    //         $arrays = [
    //             $this->input('sales_distribution', []),
    //             $this->input('material_management', []),
    //             $this->input('plant_maintenance', []),
    //             $this->input('financial_accounting_costing', []),
    //             $this->input('production_planning', []),
    //             $this->input('human_resource', []),
    //         ];

    //         // Check if at least one array is non-empty
    //         $isValid = collect($arrays)->filter(fn($array) => !empty($array))->isNotEmpty();

    //         if (!$isValid) {
    //             $validator->errors()->add('at_least_one', 'At least one of the sections must contain data.');
    //         }
    //     });
    // }

    public function messages(): array
    {
        return [
            'sales_distribution.array' => 'Sales Distribution must be an array.',
            'material_management.array' => 'Material Management must be an array.',
            'plant_maintenance.array' => 'Plant Maintenance must be an array.',
            'financial_accounting_costing.array' => 'Financial Accounting and Costing must be an array.',
            'production_planning.array' => 'Production Planning must be an array.',
            'human_resource.array' => 'Human Resource must be an array.',
        ];
    }
}
