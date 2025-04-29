<?php

namespace Database\Seeders;

use App\Models\Approver;
use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApproversTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Form::find(1)) {
            $approversData = [
                ['name' => 'Approver 1'],
                ['name' => 'Approver 2'],
                ['name' => 'Approver 3'],
                ['name' => 'Approver 4'],
                ['name' => 'Approver 5'],
            ];
            $i = 1;
            foreach ($approversData as $approverData) {
                $approver = Approver::create($approverData);

                $users = User::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->get();

                $approver->users()->attach($users, [
                    'approval_required' => 1,
                    'sequence_no' => $i++,
                ]);
            }
        }
    }
}
