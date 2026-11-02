<?php

declare(strict_types=1);

namespace Tests\Feature\Plants;

use App\Models\Plant;
use App\Models\PlantEntry;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\TestCase;

final class PlantDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-02 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_plants_are_ordered_from_newest(): void
    {
        $oldPlant = Plant::factory()->create([
            'created_at' => '2026-10-01 10:00:00',
            'updated_at' => '2026-10-01 10:00:00',
        ]);

        $newPlant = Plant::factory()->create([
            'created_at' => '2026-11-01 10:00:00',
            'updated_at' => '2026-11-01 10:00:00',
        ]);

        $response = $this->get(route('plants.index'));

        $response
            ->assertOk()
            ->assertViewHas(
                'plants',
                function (LengthAwarePaginator $plants) use (
                    $newPlant,
                    $oldPlant,
                ): bool {
                    return $plants
                        ->getCollection()
                        ->pluck('id')
                        ->all() === [
                            $newPlant->id,
                            $oldPlant->id,
                        ];
                },
            );
    }

    public function test_plants_are_paginated_by_fifty(): void
    {
        Plant::factory()->count(51)->create();

        $response = $this->get(route('plants.index'));

        $response
            ->assertOk()
            ->assertViewHas(
                'plants',
                function (LengthAwarePaginator $plants): bool {
                    return $plants->count() === 50
                        && $plants->total() === 51
                        && $plants->lastPage() === 2;
                },
            );
    }

    public function test_watering_ranking_is_sorted_by_prediction_date(): void
    {
        $later = $this->createPlantWithWaterings(
            intervalDays: 5,
            dates: ['2026-10-20 10:00:00'],
        );

        $sooner = $this->createPlantWithWaterings(
            intervalDays: 2,
            dates: ['2026-10-20 10:00:00'],
        );

        $response = $this->get(route('plants.index'));

        $response
            ->assertOk()
            ->assertViewHas(
                'topWateringPlants',
                function (Collection $items) use (
                    $sooner,
                    $later,
                ): bool {
                    return $this->plantIds($items) === [
                        $sooner->id,
                        $later->id,
                    ];
                },
            );
    }

    public function test_unavailable_plants_are_excluded_from_rankings(): void
    {
        $unavailable = Plant::factory()->create([
            'plant_type' => 'manual',
            'watering_interval_days' => 7,
        ]);

        $response = $this->get(route('plants.index'));

        $response
            ->assertOk()
            ->assertViewHas(
                'topWateringPlants',
                fn (Collection $items): bool => ! in_array(
                    $unavailable->id,
                    $this->plantIds($items),
                    true,
                ),
            )
            ->assertViewHas(
                'topUnstablePlants',
                fn (Collection $items): bool => ! in_array(
                    $unavailable->id,
                    $this->plantIds($items),
                    true,
                ),
            )
            ->assertViewHas(
                'topWaterRetainingPlants',
                fn (Collection $items): bool => ! in_array(
                    $unavailable->id,
                    $this->plantIds($items),
                    true,
                ),
            );
    }

    public function test_watering_ranking_contains_at_most_six_plants(): void
    {
        foreach (range(1, 7) as $day) {
            $this->createPlantWithWaterings(
                intervalDays: $day,
                dates: ['2026-10-20 10:00:00'],
            );
        }

        $this->get(route('plants.index'))
            ->assertOk()
            ->assertViewHas(
                'topWateringPlants',
                fn (Collection $items): bool => $items->count() === 6,
            );
    }

    public function test_instability_ranking_is_sorted_by_score(): void
    {
        $lessUnstable = $this->createPlantWithWaterings(
            intervalDays: 10,
            dates: [
                '2026-10-20 10:00:00',
                '2026-10-21 10:00:00',
            ],
        );

        $moreUnstable = $this->createPlantWithWaterings(
            intervalDays: 10,
            dates: [
                '2026-10-20 10:00:00',
                '2026-10-21 10:00:00',
                '2026-10-22 10:00:00',
            ],
        );

        $response = $this->get(route('plants.index'));

        $response
            ->assertOk()
            ->assertViewHas(
                'topUnstablePlants',
                function (Collection $items) use (
                    $moreUnstable,
                    $lessUnstable,
                ): bool {
                    return $this->plantIds($items) === [
                        $moreUnstable->id,
                        $lessUnstable->id,
                    ];
                },
            );
    }

    public function test_retention_ranking_is_sorted_by_score(): void
    {
        $lessRetaining = $this->createPlantWithWaterings(
            intervalDays: 2,
            dates: [
                '2026-10-01 10:00:00',
                '2026-10-04 10:00:00',
            ],
            moisture: 30,
        );

        $moreRetaining = $this->createPlantWithWaterings(
            intervalDays: 2,
            dates: [
                '2026-10-01 10:00:00',
                '2026-10-06 10:00:00',
            ],
            moisture: 30,
        );

        $response = $this->get(route('plants.index'));

        $response
            ->assertOk()
            ->assertViewHas(
                'topWaterRetainingPlants',
                function (Collection $items) use (
                    $moreRetaining,
                    $lessRetaining,
                ): bool {
                    return $this->plantIds($items) === [
                        $moreRetaining->id,
                        $lessRetaining->id,
                    ];
                },
            );
    }

    /**
     * @param  list<string>  $dates
     */
    private function createPlantWithWaterings(
        int $intervalDays,
        array $dates,
        ?float $moisture = null,
    ): Plant {
        $plant = Plant::factory()->create([
            'plant_type' => 'manual',
            'watering_interval_days' => $intervalDays,
            'soil_moisture_min' => 10,
            'soil_moisture_ideal_min' => 20,
        ]);

        foreach ($dates as $date) {
            PlantEntry::factory()
                ->for($plant)
                ->watering()
                ->create([
                    'recorded_at' => $date,
                    'moist_pct' => $moisture,
                ]);
        }

        return $plant;
    }

    /**
     * @param Collection<int, array{
     *     plant: Plant,
     *     prediction?: array<string, mixed>,
     *     instability?: array<string, mixed>,
     *     retention?: array<string, mixed>
     * }> $items
     * @return list<int>
     */
    private function plantIds(Collection $items): array
    {
        return $items
            ->map(
                fn (array $item): int => (int) $item['plant']->id,
            )
            ->values()
            ->all();
    }

    public function test_old_entries_are_not_used_in_dashboard_rankings(): void
    {
        $plant = $this->createPlantWithWaterings(
            intervalDays: 5,
            dates: ['2026-01-01 10:00:00'],
        );

        $this->get(route('plants.index'))
            ->assertOk()
            ->assertViewHas(
                'topWateringPlants',
                fn (Collection $items): bool => ! in_array(
                    $plant->id,
                    $this->plantIds($items),
                    true,
                ),
            );
    }

    public function test_ranking_cache_changes_after_adding_entry(): void
    {
        $plant = Plant::factory()->create([
            'plant_type' => 'manual',
            'watering_interval_days' => 5,
        ]);

        $this->get(route('plants.index'))
            ->assertViewHas(
                'topWateringPlants',
                fn (Collection $items): bool => ! in_array(
                    $plant->id,
                    $this->plantIds($items),
                    true,
                ),
            );

        PlantEntry::factory()
            ->for($plant)
            ->watering()
            ->create([
                'recorded_at' => '2026-11-01 10:00:00',
            ]);

        $this->get(route('plants.index'))
            ->assertViewHas(
                'topWateringPlants',
                fn (Collection $items): bool => in_array(
                    $plant->id,
                    $this->plantIds($items),
                    true,
                ),
            );
    }
}
