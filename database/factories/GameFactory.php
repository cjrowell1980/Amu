<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GameFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numerify('##'),
            'name' => ucwords($name),
            'description' => $this->faker->sentence(),
            'module_class' => null,
            'enabled' => true,
            'supports_teams' => $this->faker->boolean(30),
            'min_players' => 2,
            'max_players' => $this->faker->randomElement([4, 6, 8]),
            'default_config' => [],
            'version' => '1.0.0',
        ];
    }

    public function disabled(): static
    {
        return $this->state(['enabled' => false]);
    }
}
