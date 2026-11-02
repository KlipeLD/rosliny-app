<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlantEntrySource;
use App\Models\Plant;
use App\Models\PlantEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantEntry>
 */
final class PlantEntryFactory extends Factory
{
    protected $model = PlantEntry::class;

    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'source' => PlantEntrySource::Soil,
            'payload' => [],
            'note' => fake()->optional()->sentence(),
            'recorded_at' => fake()->dateTimeBetween('-30 days'),
            'current_photo_path' => null,
            'ts_ms' => fake()->optional()->numberBetween(1, 1_000_000),
            'temp_c' => fake()->randomFloat(2, 10, 35),
            'moist_pct' => fake()->randomFloat(2, 0, 100),
            'ec_uscm' => fake()->numberBetween(100, 2000),
            'ph' => fake()->randomFloat(2, 4, 9),
            'n_mgkg' => fake()->numberBetween(0, 150),
            'p_mgkg' => fake()->numberBetween(0, 100),
            'k_mgkg' => fake()->numberBetween(0, 200),
            'salt_mgl' => fake()->numberBetween(0, 500),
        ];
    }

    public function soil(): self
    {
        return $this->state([
            'source' => PlantEntrySource::Soil,
        ]);
    }

    public function watering(): self
    {
        return $this->careEntry(PlantEntrySource::Watering);
    }

    public function fertilizing(): self
    {
        return $this->careEntry(PlantEntrySource::Fertilizing);
    }

    public function repotting(): self
    {
        return $this->careEntry(PlantEntrySource::Repotting);
    }

    private function careEntry(PlantEntrySource $source): self
    {
        return $this->state([
            'source' => $source,
            'payload' => [],
            'ts_ms' => null,
            'temp_c' => null,
            'moist_pct' => null,
            'ec_uscm' => null,
            'ph' => null,
            'n_mgkg' => null,
            'p_mgkg' => null,
            'k_mgkg' => null,
            'salt_mgl' => null,
        ]);
    }
}
