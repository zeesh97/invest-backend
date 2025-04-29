<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MobileRequisitionRequest extends FormRequest
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
            'issue_date' => 'nullable|date',
            'recieve_date' => 'nullable|date',
            'make' => 'nullable|string|min:1',
            'model' => 'nullable|string|min:1',
            'imei' => 'nullable|string|min:1',
            'mobile_number' => 'nullable|string|min:1',
            'remarks' => 'required|string|min:1',
            'request_for_user_id' => 'required|exists:users,id',
            'save_as_draft' => 'in:true,false',
        ];
    }
}
