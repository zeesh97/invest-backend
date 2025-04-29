<?php

namespace App\Http\Requests;

use App\Models\Form;
use App\Models\Forms\Deployment;
use App\Services\SettingService;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeploymentRequest extends FormRequest
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
            'location_id' => 'required|exists:locations,id',
            'project_id' => 'nullable|exists:projects,id',

            'document_details' => 'required|array',
            'document_details.*.document_no' => 'required|min:1|max:1000',
            'document_details.*.detail' => 'required|min:1|max:1000',
        ];

        $reqOrOpt = 'required';
        if ($this->input('save_as_draft') === 'true') {
            $reqOrOpt = 'nullable';
        }
        $rules = array_merge($rules, [
            'reference_form_id' => $reqOrOpt . '|exists:forms,id|max:10000',
            // 'reference_details' =>  'nullable|string|max:10000',
            'change_priority' => $reqOrOpt . '|string|max:255',
            // 'reference_details' => $reqOrOpt . '|string|max:255|',
            'reference_details' => [
                "$reqOrOpt",
                'numeric',
                function ($attribute, $value, $fail) {
                    $form = Form::findOrFail($this->reference_form_id);
                    if (class_exists($form->identity)) {
                        $exists = $form->identity::where('id', $value)->exists();
                        if (!$exists) {
                            $fail('The Reference details is invalid');
                        }
                    }
                },
            ],

            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|mimes:' . $allowedExtensionsString . '|max:' . $this->settingService->getMaxUploadSize(),
        ]);


        return $rules;
    }

    public function messages()
    {
        return [
            'reference_details.in' => 'The reference detail field is invalid.',
            'process_efficiency.required' => 'The Business Process Change field is required.',
            'change_significance.required' => 'The change significance field is required.',
            'change_significance.in' => 'The selected change significance is invalid. Valid options are Minor or Major.',
            'change_significance.uppercase' => 'The first letter of change significance must be in uppercase.',
            'document_details.required' => 'The documentDetail are required.',
            'document_details.*.detail.required' => 'The documentDetail detail field is required.',
            'document_details.*.document_no.required' => 'The documentDetail document_no field is required.',
            // 'document_details.in' => 'The selected documentDetail is invalid. Valid options are Pass or Fail.',
            // 'document_details.uppercase' => 'The first letter of documentDetail must be in uppercase.'
        ];
    }
}
