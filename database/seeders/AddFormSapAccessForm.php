<?php

namespace Database\Seeders;

use App\Models\Form;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddFormSapAccessForm extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(!Form::find(7)) {
            DB::table('forms')->insertOrIgnore([
                'id' => 7, // Manually setting the ID
                'name' => 'Sap Access Form',
                'slug' => null,
                'identity' => "App\\Models\\Forms\\SapAccessForm",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
