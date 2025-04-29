<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Helpers\Helper;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['string', 'max:50'],
            'email' => ['email', 'max:50', Rule::unique('users')->ignore($this->user)],
            'password' => ['confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['sometimes', 'required_with:password', 'same:password'],
            'employee_no' => ['string', 'max:50', Rule::unique('users')->ignore($this->user)],
            'department_id' => ['integer', Rule::exists('departments', 'id')],
            'location_id' => ['integer', Rule::exists('locations', 'id')],
            'designation_id' => ['integer', Rule::exists('designations', 'id')],
            'section_id' => ['integer', Rule::exists('sections', 'id')],
            'employee_type' => ['string', 'max:50'],
            'extension' => ['nullable','integer'],
            'phone_number' => ['nullable', 'regex:/^\\+?\\d{1,4}?[-.\\s]?\\(?\\d{1,3}?\\)?[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,9}$/'],
            // 'role_id' => ['sometimes', Rule::exists('roles', 'id')],
            'role_id' => ['required', 'array'],
            'role_id.*' => ['required', 'integer', Rule::exists('roles', 'id')],
            'profile_photo_path' => ['nullable', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
            'company_id' => ['integer', Rule::exists('companies', 'id')],
        ];
    }

    // public function failedValidation(Validator $validator)
    // {
    //     Helper::sendError('validation error', $validator->errors(), [], 422);
    // }
}
