<?php

namespace App\Http\Requests;

use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupportDeskRequest extends FormRequest
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
        return [
            'request_title' => 'required',
            'relevant_id' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High',
            'phone' => 'nullable|string',
            'service_required' => 'nullable|string',
            'department_id' => 'required|numeric|exists:departments,id',
            'location_id' => 'nullable|numeric|exists:locations,id',
            'service_id' => 'required|numeric|exists:services,id',
            'team_ids' => 'required|array',
            'team_ids.*' => 'numeric|exists:teams,id',
            'description' => 'required|min:3|max:2000',
            'save_as_draft' => 'required|in:true,false',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|mimes:' . $allowedExtensionsString . '|max:' . $this->settingService->getMaxUploadSize(),
        ];
    }

    public  function messages(): array
    {
        return [
            'request_title.required' => 'The request title field is required.',
            'data.required' => 'Atleast one Team is required field is required.',
        ];
    }
}
