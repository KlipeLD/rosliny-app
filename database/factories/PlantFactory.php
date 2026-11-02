<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlantType;
use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plant>
 */
final class PlantFactory extends Factory
{
    protected $model = Plant::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'plant_type' => PlantType::Manual,
            'watering_interval_days' => 7,
        ];
    }

    public function sensor(): self
    {
        return $this->state([
            'plant_type' => PlantType::Sensor,
            'soil_moisture_min' => 10,
            'soil_moisture_max' => 80,
            'soil_moisture_ideal_min' => 20,
            'soil_moisture_ideal_max' => 60,
        ]);
    }
}
