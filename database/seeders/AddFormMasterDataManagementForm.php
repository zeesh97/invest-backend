<?php

namespace Database\Seeders;

use App\Models\Form;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddFormMasterDataManagementForm extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(!Form::find(6)) {
            DB::table('forms')->insertOrIgnore([
                'id' => 6, // Manually setting the ID
                'name' => 'Master Data Management',
                'identity' => "App\\Models\\Forms\\MasterDataManagementForm",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
