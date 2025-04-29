<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Helpers\Helper;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo' => ['nullable', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:50'],
            'long_name' => ['required', 'string', 'max:50'],
            'ntn_no' => ['required', 'string', 'max:50'],
            'sales_tax_no' => ['required', 'string', 'max:50'],
            'postal_code' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:50'],
        ];
    }

    // public function failedValidation(Validator $validator)
    // {
    //     Helper::sendError('validation error', $validator->errors(), [], 422);
    // }
}
