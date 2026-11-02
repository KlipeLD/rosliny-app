<?php

declare(strict_types=1);

namespace Tests\Unit\Plants;

use App\Models\Plant;
use App\Models\PlantEntry;
use App\Services\Plants\WateringEventDetector;
use App\Services\Plants\WateringPredictionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

final class WateringPredictionTest extends TestCase
{
    private WateringPredictionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new WateringPredictionService(new WateringEventDetector);
    }

    public function test_prediction_is_unavailable_without_entries(): void
    {
        $plant = $this->plant([
            'watering_interval_days' => 5,
        ]);

        $prediction = $this->service->predict($plant, collect());

        $this->assertFalse($prediction['available']);
        $this->assertArrayHasKey('reason', $prediction);
    }

    public function test_prediction_is_unavailable_with_one_event_and_no_interval(): void
    {
        $plant = $this->plant([
            'watering_interval_days' => null,
        ]);

        $prediction = $this->service->predict($plant, collect([
            $this->entry('watering', '2026-04-01 10:00:00'),
        ]));

        $this->assertFalse($prediction['available']);
    }

    public function test_configured_interval_produces_calendar_prediction(): void
    {
        $plant = $this->plant([
            'watering_interval_days' => 5,
        ]);

        $prediction = $this->service->predict($plant, collect([
            $this->entry('watering', '2026-04-01 10:00:00'),
        ]));

        $this->assertTrue($prediction['available']);
        $this->assertSame('calendar', $prediction['mode']);
        $this->assertSame(
            '2026-04-06 10:00:00',
            $prediction['date']->format('Y-m-d H:i:s'),
        );
        $this->assertSame(
            '2026-04-01 10:00:00',
            $prediction['last_watering_at']->format('Y-m-d H:i:s'),
        );
    }

    public function test_average_interval_produces_estimated_prediction(): void
    {
        $plant = $this->plant([
            'watering_interval_days' => null,
        ]);

        $prediction = $this->service->predict($plant, collect([
            $this->entry('watering', '2026-04-01 10:00:00'),
            $this->entry('watering', '2026-04-05 10:00:00'),
            $this->entry('watering', '2026-04-11 10:00:00'),
        ]));

        // Odstępy: 4 i 6 dni, średnia: 5 dni.
        $this->assertTrue($prediction['available']);
        $this->assertSame('estimated', $prediction['mode']);
        $this->assertSame(2, $prediction['samples']);
        $this->assertSame(
            '2026-04-16 10:00:00',
            $prediction['date']->format('Y-m-d H:i:s'),
        );
    }

    public function test_manual_plant_ignores_low_soil_measurement(): void
    {
        $plant = $this->plant([
            'watering_interval_days' => 2,
        ]);

        $prediction = $this->service->predict($plant, collect([
            $this->entry('soil', '2026-04-01 10:00:00', 5),
        ]));

        $this->assertFalse($prediction['available']);
    }

    public function test_sensor_measurement_below_ideal_minimum_is_watering_event(): void
    {
        $plant = $this->sensorPlant();

        $prediction = $this->service->predict($plant, collect([
            $this->entry('soil', '2026-04-01 10:00:00', 19.99),
        ]));

        $this->assertTrue($prediction['available']);
        $this->assertSame(
            '2026-04-03 10:00:00',
            $prediction['date']->format('Y-m-d H:i:s'),
        );
    }

    public function test_measurement_equal_to_ideal_minimum_is_not_watering_event(): void
    {
        $plant = $this->sensorPlant();

        $prediction = $this->service->predict($plant, collect([
            $this->entry('soil', '2026-04-01 10:00:00', 20),
        ]));

        $this->assertFalse($prediction['available']);
    }

    public function test_ideal_measurement_before_prediction_does_not_delay_it(): void
    {
        $plant = $this->sensorPlant();

        $prediction = $this->service->predict($plant, collect([
            $this->entry('watering', '2026-04-12 09:00:00'),
            $this->entry('soil', '2026-04-13 18:00:00', 35),
        ]));

        $this->assertTrue($prediction['available']);
        $this->assertSame(
            '2026-04-14 09:00:00',
            $prediction['date']->format('Y-m-d H:i:s'),
        );
    }

    public function test_ideal_measurement_on_prediction_day_delays_to_next_day(): void
    {
        $plant = $this->sensorPlant();

        $prediction = $this->service->predict($plant, collect([
            $this->entry('soil', '2026-04-12 09:00:00', 8),
            $this->entry('soil', '2026-04-14 18:00:00', 35),
        ]));

        $this->assertTrue($prediction['available']);
        $this->assertSame(
            '2026-04-15 18:00:00',
            $prediction['date']->format('Y-m-d H:i:s'),
        );
    }

    public function test_sensor_plant_uses_manual_watering_event(): void
    {
        $plant = $this->sensorPlant();

        $prediction = $this->service->predict($plant, collect([
            $this->entry('soil', '2026-04-11 09:00:00', 8),
            $this->entry('watering', '2026-04-14 18:00:00'),
        ]));

        $this->assertTrue($prediction['available']);
        $this->assertSame(
            '2026-04-16 18:00:00',
            $prediction['date']->format('Y-m-d H:i:s'),
        );
    }

    private function plant(array $attributes = []): Plant
    {
        return new Plant([
            'plant_type' => 'manual',
            'soil_moisture_ideal_min' => 20,
            ...$attributes,
        ]);
    }

    private function sensorPlant(): Plant
    {
        return new Plant([
            'plant_type' => 'sensor',
            'soil_moisture_ideal_min' => 20,
            'watering_interval_days' => 2,
        ]);
    }

    private function entry(
        string $source,
        string $date,
        ?float $moisture = null,
    ): PlantEntry {
        return new PlantEntry([
            'source' => $source,
            'recorded_at' => Carbon::parse($date),
            'moist_pct' => $moisture,
        ]);
    }

    public function test_ideal_measurement_after_overdue_date_delays_prediction(): void
    {
        $plant = $this->sensorPlant();

        $prediction = $this->service->predict($plant, collect([
            $this->entry('soil', '2026-04-11 09:00:00', 8),
            $this->entry('soil', '2026-04-14 18:00:00', 35),
        ]));

        $this->assertTrue($prediction['available']);
        $this->assertSame(
            '2026-04-15 18:00:00',
            $prediction['date']->format('Y-m-d H:i:s'),
        );
    }

    public function test_accepts_eloquent_collection_with_manual_sensor_event(): void
    {
        $plant = $this->sensorPlant();

        $entries = new EloquentCollection([
            $this->entry(
                'watering',
                '2026-04-14 18:00:00',
            ),
        ]);

        $prediction = $this->service->predict($plant, $entries);

        $this->assertTrue($prediction['available']);
        $this->assertSame(
            '2026-04-16 18:00:00',
            $prediction['date']->format('Y-m-d H:i:s'),
        );
    }
}
