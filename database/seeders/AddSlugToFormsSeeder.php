<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AddSlugToFormsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Form::whereNull('slug')->first()) {
            foreach (Form::all() as $form) {
                $baseName = class_basename($form->identity);
                $slug = preg_replace_callback('/[A-Z]{2,}/', function ($matches) {
                    return strtolower($matches[0]);
                }, $baseName);

                $slug = Str::kebab($slug);


                $i = 1;
                while (Form::where('slug', $slug)->where('id', '!=', $form->id)->exists()) {
                    $slug = Str::kebab($baseName) . '-' . $i++;
                }

                $form->slug = $slug;
                $form->save();
            }
        }
    }
}
