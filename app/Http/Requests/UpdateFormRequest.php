<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormRequest extends FormRequest
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
            // 'name' => 'required|unique:forms,name,' . $this->route('form'),
            // 'identity' => 'required|unique:forms,identity,' . $this->route('form'),
            'initiator_field_one_id' => 'nullable|exists:setup_fields,id|different:initiator_field_two_id,initiator_field_three_id,initiator_field_four_id,initiator_field_five_id',
            'initiator_field_two_id' => 'nullable|exists:setup_fields,id|different:initiator_field_one_id,initiator_field_three_id,initiator_field_four_id,initiator_field_five_id',
            'initiator_field_three_id' => 'nullable|exists:setup_fields,id|different:initiator_field_one_id,initiator_field_two_id,initiator_field_four_id,initiator_field_five_id',
            'initiator_field_four_id' => 'nullable|exists:setup_fields,id|different:initiator_field_one_id,initiator_field_two_id,initiator_field_three_id,initiator_field_five_id',
            'initiator_field_five_id' => 'nullable|exists:setup_fields,id|different:initiator_field_one_id,initiator_field_two_id,initiator_field_three_id,initiator_field_four_id',
            'callback' => 'nullable|string|max:255',
        ];
    }
}
