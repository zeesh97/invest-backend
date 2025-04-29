<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeploymentFormUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $form = Form::find(3);
        if ($form->name != 'Deployment') {
            $form->update([
                'identity' => 'App\\Models\\Forms\\Deployment',
                'name' => 'Deployment',
                'slug' => 'deployment'
            ]);
        }

    }
}
