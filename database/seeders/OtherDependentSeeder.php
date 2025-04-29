<?php

namespace Database\Seeders;

use App\Models\OtherDependent;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OtherDependentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!OtherDependent::where('type', 'crf')->first()) {
            OtherDependent::create([
                'type' => 'crf',
                'data' => [
                    'capital_max_amount' => '1000000',
                    'revenue_max_amount' => '3000000'
                ],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
