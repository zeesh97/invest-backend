<?php

namespace Database\Factories;

use App\Models\Forms\SCRF;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::get()->random()->id,
            'scrf_id' => SCRF::get()->random()->id,
            'comment' => $this->faker->paragraph(1)
        ];
    }
}
