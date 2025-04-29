<?php

namespace App\Http\Requests;

use App\Models\Form;
use Illuminate\Foundation\Http\FormRequest;

class QaAssignmentRequest extends FormRequest
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
            'qa_user_ids' => 'array|required|min:1|max:10',
            'qa_user_ids.*' => 'exists:users,id|distinct',
            'form_id' => 'required|exists:forms,id',
            'key' => [
                'required',
                function ($attribute, $value, $fail){
                    $model = Form::findOrFail($this->form_id)->identity;
                    $record = $model::find($value)->exists();
                    if (!$record) {
                        $fail('The specified key is invalid for form ID ' . $this->form_id . '. Please check the form definition.');
                    }
                },
            ],
            // 'status' => 'required|in:Ok,Modification Required',
            // 'feedback' => 'required|string|max:1000',
        ];
    }
}
