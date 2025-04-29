<?php

namespace App\Http\Requests;

use App\Models\Forms\SCRF;
use App\Services\SettingService;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreMasterDataManagementFormRequest extends FormRequest
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
            'request_title' => 'required|string|max:255',
            // 'change_significance' => 'required|in:Minor,Major,minor,major',
            'mdm_project_id' => 'nullable|exists:project_mdm_software_categories,id',
            'software_category_id' => 'required|integer|exists:software_categories,id',
            'mdm_category_id' => 'required|integer|exists:mdm_categories,id',
            'software_subcategory_id' => 'required|array|exists:software_subcategories,id',
            'location_id' => 'required|exists:locations,id',
            'uat_scenarios' => 'nullable|array',
            'uat_scenarios.*.status' => 'nullable|in:Pass,Fail',
            'uat_scenarios.*.detail' => 'nullable|min:1|max:1000',
        ];

        $reqOrOpt = 'required';
        if ($this->input('save_as_draft') === 'true') {
            $reqOrOpt = 'nullable';
        }
        $rules = array_merge($rules, [
            'request_specs' => $reqOrOpt . '|string|max:2000',
            'change_priority' => $reqOrOpt . '|string|max:2000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|mimes:' . $allowedExtensionsString . '|max:' . $this->settingService->getMaxUploadSize(),
        ]);


        return $rules;
    }

    public function messages()
    {
        return [
            'process_efficiency.required' => 'The Business Process Change field is required.',
            // 'change_significance.required' => 'The change significance field is required.',
            // 'change_significance.in' => 'The selected change significance is invalid. Valid options are Minor or Major.',
            // 'change_significance.uppercase' => 'The first letter of change significance must be in uppercase.',
            // 'uat_scenarios.required' => 'The UAT scenarios are required.',
            // 'uat_scenarios.*.detail.required' => 'The UAT scenario detail field is required.',
            // 'uat_scenarios.*.status.required' => 'The UAT scenario status field is required.',
            'uat_scenarios.in' => 'The selected UAT scenarios is invalid. Valid options are Pass or Fail.',
            'uat_scenarios.uppercase' => 'The first letter of UAT scenarios must be in uppercase.'
        ];
    }
}
