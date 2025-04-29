<?php

namespace App\Http\Requests;

use App\Enums\FormEnum;
use App\Models\ApprovalStatus;
use App\Models\Forms\MasterDataManagementForm;
use App\Services\SettingService;
use App\Traits\UserEditPermission;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Str;

class UpdateMasterDataManagementFormRequest extends FormRequest
{
    use UserEditPermission;
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
        return $this->ifInitiatedByMe(get_class());
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
            'software_category_id' => 'required|exists:software_categories,id',
            'mdm_project_id' => 'nullable|exists:projectMDM,id',
            'mdm_category_id' => 'required|exists:mdm_categories,id',
            'software_subcategory_id' => 'required|array|exists:software_subcategories,id',
            'location_id' => 'required|exists:locations,id',
            'uat_scenarios' => 'nullable|array',
            'uat_scenarios.*.status' => 'nullable|in:Pass,Fail',
            'uat_scenarios.*.detail' => 'nullable|min:1|max:1000',
        ];

        // Add other rules if save_as_draft is false
        $reqOrOpt = 'required';
        if ($this->input('save_as_draft') === 'true') {
            $reqOrOpt = 'nullable';
        }
        $rules = array_merge($rules, [
            'request_specs' => $reqOrOpt . '|string|max:2000',
            'change_priority' => $reqOrOpt . '|string|max:2000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|mimes:' . $allowedExtensionsString . '|max:' . $this->settingService->getMaxUploadSize(),
            'deleted_attachments' => 'sometimes|array',
            'deleted_attachments.*' => 'sometimes|exists:attachments,id',
        ]);


        return $rules;
    }

    public function messages()
    {
        return [
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
