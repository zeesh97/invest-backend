<?php

namespace Database\Seeders;

use App\Models\SoftwareCategory;
use App\Models\SoftwareRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesAddedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SoftwareCategory::firstOrCreate(['name' => 'Sap Internal']);
        SoftwareCategory::firstOrCreate(['name' => 'Sap Sales Group']);
    }
}
