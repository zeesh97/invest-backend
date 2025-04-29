<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
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
            'name' => 'required| string| min:2| max:60|'.Rule::unique('teams','id')->ignore($this->id),
            'form_ids' => ['required', 'array'],
            'form_ids*' => ['required', 'exists:forms,id'],
            'location_ids' => ['required', 'array'],
            'location_ids*' => ['required', 'exists:locations,id'],
            'manager_ids' => ['required', 'array'],
            'manager_ids*' => ['required', 'exists:users,id']
        ];
    }
}
