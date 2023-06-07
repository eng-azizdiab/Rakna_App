<?php

namespace Database\Factories;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Parking>
 */
class ParkingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $minLatitude = 22.0;
        $maxLatitude = 31.6;
        $minLongitude = 24.7;
        $maxLongitude = 36.9;

        $latitude = $this->faker->randomFloat(6, $minLatitude, $maxLatitude);
        $longitude = $this->faker->randomFloat(6, $minLongitude, $maxLongitude);

        return [
            'name'=> fake()->city,
//            'type' => fake()->randomElement(['restaurant', 'bar', 'hotel', 'park','museum', 'art gallery', 'historic site']),
            'location' => DB::raw("(POINT($longitude, $latitude))"),
            'price_per_hour'=>fake()->randomElement([10,20,30]),
            'number_of_slots'=>fake()->numberBetween(100,100),
            'number_of_floors'=>fake()->randomElement([1]),
            'supplier_id'=>fake()->unique()->numberBetween(16,1320)
//            'busy_slots'=>fake()->numberBetween(30,30),

        ];

    }
}/*
  SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`rakna_app`.`parkings`, CONSTRAINT `parkings_supplier_id_foreign` FORE
IGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)) (Connection: mysql, SQL: insert into `parkings` (`name`, `location`, `price_per_hour`, `number_of_slots`, `number_of_floors`, `supplier_i
d`, `updated_at`, `created_at`) values (South Mandy, (POINT(35.53637, 26.902297)), 10, 100, 1, 320, 2023-05-03 05:57:07, 2023-05-03 05:57:07))
*/
