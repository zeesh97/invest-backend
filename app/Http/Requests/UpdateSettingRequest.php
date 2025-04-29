<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class UpdateSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        // dd($this->request->all());
        $maxUploadLimit = config('app.max_upload_limit', 10000);

        return [
            'max_upload_size' => 'required|numeric|min:1|max:'.$maxUploadLimit,
            'allowed_extensions' => 'required|array',
            'allowed_extensions.*' => 'string|regex:/^[a-zA-Z]{2,4}$/',
            'email_username' => 'required|min:3|max:60',
            'email_password' => 'required|min:3|max:60',
            'email_host' => 'required',
            'email_port' => 'required|numeric',
            'timezone' => 'required|timezone',
            'email_encryption' => 'required|min:3|max:50',
            'email_transport' => 'required|min:3|max:50',
        ];
    }
}
