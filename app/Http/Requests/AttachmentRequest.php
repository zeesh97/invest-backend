<?php

namespace App\Http\Requests;

use App\Models\Form;
use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;

class AttachmentRequest extends FormRequest
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
        $allowedExtensions = json_decode($allowedExtensionsJson, true) ?? [];
        $allowedExtensionsString = implode(',', $allowedExtensions);

        return [
            'form_id' => 'required|exists:forms,id',
            'key' => 'required|integer|exists:' . $this->getFormData() . ',id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|mimes:' . $allowedExtensionsString . '|max:' . $this->settingService->getMaxUploadSize(),
        ];
    }


    public function getForm(): Form
    {
        return Form::findOrFail($this->form_id);
    }
    public function getFormData(): string
    {
        $model = $this->getForm()->identity;
        $class = new $model();
        return $class->getTable();
    }
}
