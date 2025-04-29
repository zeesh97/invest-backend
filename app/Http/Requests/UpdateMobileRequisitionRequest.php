<?php

namespace App\Http\Requests;

use App\Traits\UserEditPermission;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMobileRequisitionRequest extends FormRequest
{
    use UserEditPermission;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->ifInitiatedByMe(get_class());
        // $modelName = preg_match('/Update(.*)Request/', get_class(), $matches) ? $matches[1] : null;
        // $modelName = 'App\\Models\\Forms\\'.$modelName;
        // $instance = new $modelName;
        // $tableName = $instance->getTable();
        // $record = $modelName::findOrFail($this->route($tableName));
        // return (Auth::user()->id == $record->created_by && $record->status !== 'Approved' && $record->status !== 'Pending') ? true : false;
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
            'make' => 'nullable|integer|exists:makes,id',
            'imei' => 'nullable|string|min:1',
            'model' => 'nullable|string|min:1',
            'mobile_number' => 'nullable|string|min:1',
            'remarks' => 'required|string|min:1',
            'request_for_user_id' => 'required|exists:users,id',
            'save_as_draft' => 'in:true,false',
        ];
    }
}
