<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\SetupField;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SetupFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!SetupField::find(1)) {
            $entries = [
                [
                    'identity' => "App\\Models\\SoftwareCategory",
                    'name' => "Software Category",
                ],
                [
                    'identity' => "App\\Models\\SoftwareSubcategory",
                    'name' => "Software Subcategory",
                ],
                [
                    'identity' => "App\\Models\\Department",
                    'name' => "Department",
                ],
                [
                    'identity' => "App\\Models\\Designation",
                    'name' => "Designation",
                ],
                [
                    'identity' => "App\\Models\\Location",
                    'name' => "Location",
                ],
                [
                    'identity' => "App\\Models\\Section",
                    'name' => "Section",
                ],
            ];

            foreach ($entries as $entry) {
                SetupField::create($entry);
            }
        }

        SetupField::firstOrCreate(
            ['id' => 8],
            [
                'identity' => "App\\Models\\Form",
                'name' => "Form",
            ]
        );

        SetupField::firstOrCreate(
            ['id' => 9],
            [
                'identity' => "App\\Models\\Project",
                'name' => "Project",
            ]
        );
    }
}
