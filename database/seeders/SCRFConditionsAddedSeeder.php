<?php

namespace Database\Seeders;

use App\Models\Condition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SCRFConditionsAddedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Condition::find(10)) {
            Condition::insert([
                [
                    'id' => 10,
                    'name' => 'Sap Internal',
                    'form_id' => 2,
                    'created_at' => Carbon::now(),
                ]
            ]);
        }
        if (!Condition::find(11)) {
            Condition::insert([
                [
                    'id' => 11,
                    'name' => 'Sap Sales Group',
                    'form_id' => 2,
                    'created_at' => Carbon::now(),
                ]
            ]);
        }
    }
}
