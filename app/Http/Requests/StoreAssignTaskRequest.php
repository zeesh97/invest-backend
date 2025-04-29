<?php

namespace App\Http\Requests;

use App\Models\Form;
use App\Rules\FormKeyExists;
use App\Rules\ValidTeamMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssignTaskRequest extends FormRequest
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
        $rules = [
            'form_id' => 'required|integer|exists:forms,id',
            'key' => ['required', 'integer', new FormKeyExists()],
            'start_at' => 'nullable|date_format:Y-m-d H:i:s',
            'due_at' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:start_at',

            'team_ids' => 'required|array|min:1',
            'team_ids.*.team_id' => 'required|integer|exists:teams,id',
            'team_ids.*.team_members' => 'required|array|min:1',
        ];
        // return [
        //     'form_id' => 'required|integer|exists:forms,id',
        //     'key' => ['required', 'integer', new FormKeyExists()],
        //     'start_at' => 'required|date_format:Y-m-d H:i:s',
        //     'due_at' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:start_at',

        //     // Validate teams
        //     'team_ids' => 'required|array|min:1',
        //     'team_ids.*.team_id' => 'required|integer|exists:teams,id',
        //     'team_ids.*.team_members' => 'required|array|min:1',

        //     // Ensure team members exist in the `team_member` table for their respective `team_id`
        //     'team_ids.*.team_members.*' => [
        //         'required',
        //         'integer',
        //         Rule::exists('team_member', 'member_id')
        //             ->whereColumn('team_id', 'team_ids.*.team_id'),
        //     ],
        // ];
        foreach ($this->input('team_ids', []) as $index => $teamData) {
            $teamId = $teamData['team_id'] ?? null;

            if ($teamId && isset($teamData['team_members'])) {
                foreach ($teamData['team_members'] as $memberIndex => $memberId) {
                    $rules["team_ids.$index.team_members.$memberIndex"] = [
                        'required',
                        'integer',
                        new ValidTeamMember($teamId),
                    ];
                }
            }
        }

        return $rules;
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'team_ids.*.team_id.exists' => 'The selected team does not exist.',
            'team_ids.*.team_members.*.exists' => 'One or more team members are not valid for the selected team.',
        ];
    }
}
