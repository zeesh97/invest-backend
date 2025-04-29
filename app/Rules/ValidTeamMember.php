<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ValidTeamMember implements ValidationRule
{
    protected int $teamId;

    public function __construct(int $teamId)
    {
        $this->teamId = $teamId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, string): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = DB::table('team_member')
            ->where('team_id', $this->teamId)
            ->where('member_id', $value)
            ->exists();

        if (! $exists) {
            $fail('One or more team members are not valid for the selected team.');
        }
    }
}
