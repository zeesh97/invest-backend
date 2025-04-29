<?php

namespace App\Http\Requests;

use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
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
        $allowedExtensions = $this->settingService->getAllowedExtensions();
        return [
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|mimes:' . $allowedExtensions . '|max:' . $this->settingService->getMaxUploadSize(),
        ];
    }
}
