<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plat_number'=>fake()->regexify('[A-Z]{3}-[0-9]{3}'),
            'user_id'=>fake()->unique()->numberBetween(2,1007),
            'da'=>fake()->dateTimeThisDecade()
        ];
    }
}
