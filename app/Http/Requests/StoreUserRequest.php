<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Helpers\Helper;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:50', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'employee_no' => ['required', 'string', 'max:50', 'unique:users,employee_no'],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'location_id' => ['required', 'integer', Rule::exists('locations', 'id')],
            'designation_id' => ['required', 'integer', Rule::exists('designations', 'id')],
            'section_id' => ['required', 'integer', Rule::exists('sections', 'id')],
            'employee_type' => ['required', 'string', Rule::in(['Contract', 'Permanent'])],
            'extension' => ['nullable', 'numeric'],
            'phone_number' => ['nullable', 'regex:/^\\+?\\d{1,4}?[-.\\s]?\\(?\\d{1,3}?\\)?[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,9}$/'],
            // 'role_id' => ['required', 'string', Rule::exists('roles', 'id')],
            'role_id' => ['required', 'array'], // Now an array
            'role_id.*' => ['required', 'integer', Rule::exists('roles', 'id')],
            'profile_photo_path' => ['nullable', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')],

        ];
    }
    public function failedValidation(Validator $validator)
    {
        Helper::sendError('validation error', $validator->errors(), 200);
    }
}
