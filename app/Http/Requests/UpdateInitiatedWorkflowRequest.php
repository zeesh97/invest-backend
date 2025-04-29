<?php

namespace App\Http\Requests;

use App\Models\ApprovalStatus;
use App\Models\Approver;
use App\Models\Form;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInitiatedWorkflowRequest extends FormRequest
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
            'approverSubscribers' => 'nullable|array|min:1',
            'form_id' => 'required|exists:forms,id',
            'key' => [
                'required',
                function ($attribute, $value, $fail) {
                    $form = Form::find($this->input('form_id'));
                    if ($form && !$form->identity::where('id', $value)->exists()) {
                        $fail('The selected key is invalid for this form.');
                    }
                },
            ],
            'approverSubscribers.*.approver_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    // $approver = Approver::where('id', $value)->exists();
                    $inApprovalStatus = ApprovalStatus::where('form_id', $this->form_id)
                        ->where('key', $this->key)
                        ->where('approver_id', $value)
                        ->exists();
                    // dd($inApprovalStatus);
                    if ($inApprovalStatus) {
                        $fail('The selected key is invalid for this form.');
                    }
                }
            ],
            'approverSubscribers.*.subscriber_id' => 'nullable|exists:subscribers,id',
            'approverSubscribers.*.approval_condition' => 'nullable|exists:conditions,id',
            'approverSubscribers.*.sequence_no' => [
                'required',
                'integer',
                Rule::unique('approval_statuses')->where(function ($query) {
                    return $query->where('form_id', $this->form_id)
                        ->where('key', $this->key)
                        ->where('approver_id', $this->approver_id);
                }),
            ],
            'workflowSubscribersApprovers.*.editable' => 'nullable|boolean',
        ];
    }
}
