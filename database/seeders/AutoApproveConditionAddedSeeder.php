<?php

namespace Database\Seeders;

use App\Models\Condition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AutoApproveConditionAddedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Condition::find(12)) {
            Condition::insert([
                [
                    'id' => 12,
                    'name' => 'Auto Approve',
                    'form_id' => 1,
                    'created_at' => Carbon::now(),
                ]
            ]);
        }
    }
}
