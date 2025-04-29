<?php

namespace Database\Seeders;

use App\Models\TaskStatusName;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskStatusNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(!TaskStatusName::first())
        {
            TaskStatusName::insert([
                ['name' => 'Open'],
                ['name' => 'In Process'],
                ['name' => 'Development'],
                ['name' => 'UAT'],
                ['name' => 'UFA - User Feedback Awaited'],
                ['name' => 'Closed'],
                ['name' => 'Rejected & Closed'],
            ]);
        }
    }
}
