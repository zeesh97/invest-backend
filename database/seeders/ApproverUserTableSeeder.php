<?php

namespace Database\Seeders;

use App\Models\Approver;
use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApproverUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Form::find(1)) {
            $approverUserAssignments = [
                ['approver_id' => 1, 'user_id' => 1, 'approval_required' => 1, 'sequence_no' => 1],
                ['approver_id' => 2, 'user_id' => 2, 'approval_required' => 1, 'sequence_no' => 2]
            ];

            foreach ($approverUserAssignments as $assignment) {
                $approver = Approver::find($assignment['approver_id']);
                $user = User::find($assignment['user_id']);

                if ($approver && $user) {
                    $approver->users()->attach($user, [
                        'approval_required' => $assignment['approval_required'],
                        'sequence_no' => $assignment['sequence_no'],
                    ]);
                }
            }
        }
    }
}
