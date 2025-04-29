<?php

namespace App\Http\Requests;

use App\Rules\FormKeyExists;
use App\Rules\ValidTeamMember;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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

    public function messages(): array
    {
        return [
            'team_ids.*.team_id.exists' => 'The selected team does not exist.',
            'team_ids.*.team_members.*.exists' => 'One or more team members are not valid for the selected team.',
        ];
    }
}

