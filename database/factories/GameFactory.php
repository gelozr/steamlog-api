<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(20),
            'short_description' => $this->faker->text(100),
            'genre' => $this->faker->word(),
            'release_date' => $this->faker->date(),
        ];
    }
}
