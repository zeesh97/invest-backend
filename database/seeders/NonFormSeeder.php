<?php

namespace Database\Seeders;

use App\Models\NonForm;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NonFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!NonForm::find(1)) {
            NonForm::insert([
                [
                    'name' => 'Request Support Form',
                    'identity' => "App\\Models\\RequestSupportForm",
                    'slug' => "request-support-form",
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
        }
        $record = NonForm::find(1);
        if($record){
            $record->update([
                'name' => 'Request Support Form',
                'identity' => "App\\Models\\RequestSupportForm",
                'slug' => "request-support-form",
            ]);
        }
    }
}
