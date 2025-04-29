<?php

namespace App\Http\Requests;

use App\Models\NonForm;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Comment;
use App\Services\SettingService;
use Illuminate\Validation\Rule;

class NonCommentStoreRequest extends FormRequest
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
        parent::__construct();
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
       $baseRules = [
        'attachments' => 'nullable|array',
        'attachments.*' => 'nullable|mimes:' . $allowedExtensionsString . '|max:' . $this->settingService->getMaxUploadSize(),
            'comment' => 'required|min:1|max:1000',
            // 'mentioned_user_ids' => 'nullable|array',
            // 'mentioned_user_ids.*' => 'nullable|integer|exists:users,id',
            'non_form_id' => 'required|integer|exists:non_forms,id',
            'key' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $form = NonForm::find($this->non_form_id);
                    if (!$form) {
                        $fail('The non form_id does not exist.');
                        return;
                    }

                    $modelName = $form->identity;
                    if (!class_exists($modelName)) {
                        $fail('The referenced model does not exist.');
                        return;
                    }

                    $model = new $modelName();
                    $tableName = $model->getTable();

                    if (!$model->where('id', $value)->exists()) {
                        $fail('The key does not exist in the referenced model.');
                    }
                },
            ],
        ];

        // $attachmentRequest = new StoreAttachmentRequest($this->settingService);
        // $attachmentRules = $attachmentRequest->rules();

        // return array_merge($baseRules, $attachmentRules);
        return $baseRules;
    }
}
