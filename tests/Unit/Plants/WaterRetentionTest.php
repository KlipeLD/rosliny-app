<?php

declare(strict_types=1);

namespace Tests\Unit\Plants;

use App\Models\Plant;
use App\Models\PlantEntry;
use App\Services\Plants\WateringEventDetector;
use App\Services\Plants\WaterRetentionService;
use Carbon\Carbon;
use Tests\TestCase;

final class WaterRetentionTest extends TestCase
{
    private WaterRetentionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new WaterRetentionService(new WateringEventDetector);
        Carbon::setTestNow('2026-05-01 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_result_is_unavailable_without_interval(): void
    {
        $plant = $this->plant([
            'watering_interval_days' => null,
        ]);

        $result = $this->service->analyze($plant, collect([
            $this->entry('watering', '2026-04-01 09:00:00', 30),
            $this->entry('watering', '2026-04-10 09:00:00', 30),
        ]));

        $this->assertFalse($result['available']);
        $this->assertSame(60, $result['period_days']);
    }

    public function test_result_is_unavailable_with_one_event(): void
    {
        $result = $this->service->analyze($this->plant(), collect([
            $this->entry('watering', '2026-04-01 09:00:00', 30),
        ]));

        $this->assertFalse($result['available']);
    }

    public function test_on_time_watering_is_not_late(): void
    {
        $result = $this->service->analyze($this->plant(), collect([
            $this->entry('watering', '2026-04-01 09:00:00', 30),
            $this->entry('watering', '2026-04-06 09:00:00', 30),
        ]));

        $this->assertFalse($result['available']);
        $this->assertSame(
            'Brak podlewań po przewidywanym terminie',
            $result['reason'],
        );
    }

    public function test_late_watering_days_are_counted(): void
    {
        $result = $this->service->analyze($this->plant(), collect([
            $this->entry('watering', '2026-04-01 09:00:00', 30),
            $this->entry('watering', '2026-04-09 09:00:00', 30),
        ]));

        $this->assertTrue($result['available']);
        $this->assertSame(1, $result['late_events']);
        $this->assertSame(3, $result['late_days']);
        $this->assertSame(3, $result['score']);
        $this->assertSame(3.0, $result['average_late_days']);
    }

    public function test_multiple_late_events_are_accumulated(): void
    {
        $result = $this->service->analyze($this->plant(), collect([
            $this->entry('watering', '2026-04-01 09:00:00', 30),
            $this->entry('watering', '2026-04-09 09:00:00', 30),
            $this->entry('watering', '2026-04-17 09:00:00', 30),
        ]));

        $this->assertTrue($result['available']);
        $this->assertSame(2, $result['late_events']);
        $this->assertSame(6, $result['late_days']);
        $this->assertSame(6, $result['score']);
        $this->assertSame(3.0, $result['average_late_days']);
    }

    public function test_late_event_older_than_sixty_days_is_ignored(): void
    {
        $result = $this->service->analyze($this->plant(), collect([
            $this->entry('watering', '2026-01-01 09:00:00', 30),
            $this->entry('watering', '2026-01-10 09:00:00', 30),
        ]));

        $this->assertFalse($result['available']);
    }

    public function test_late_watering_without_moisture_evidence_is_not_counted(): void
    {
        $result = $this->service->analyze($this->plant(), collect([
            $this->entry('watering', '2026-04-01 09:00:00'),
            $this->entry('watering', '2026-04-09 09:00:00'),
        ]));

        $this->assertFalse($result['available']);
    }

    public function test_same_day_soil_measurement_confirms_retention(): void
    {
        $result = $this->service->analyze($this->plant(), collect([
            $this->entry('watering', '2026-04-01 09:00:00'),
            $this->entry('watering', '2026-04-09 09:00:00'),
            $this->entry('soil', '2026-04-09 10:00:00', 30),
        ]));

        $this->assertTrue($result['available']);
        $this->assertSame(1, $result['late_events']);
        $this->assertSame(3, $result['late_days']);
    }

    public function test_measurement_below_ideal_minimum_does_not_confirm_retention(): void
    {
        $result = $this->service->analyze($this->plant(), collect([
            $this->entry('watering', '2026-04-01 09:00:00'),
            $this->entry('watering', '2026-04-09 09:00:00'),
            $this->entry('soil', '2026-04-09 10:00:00', 15),
        ]));

        $this->assertFalse($result['available']);
    }

    private function plant(array $attributes = []): Plant
    {
        return new Plant([
            'plant_type' => 'manual',
            'soil_moisture_ideal_min' => 20,
            'watering_interval_days' => 5,
            ...$attributes,
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

    public function test_next_measurement_after_expected_date_confirms_retention(): void
    {
        $result = $this->service->analyze($this->plant(), collect([
            $this->entry('watering', '2026-04-01 09:00:00'),
            $this->entry('watering', '2026-04-09 09:00:00'),
            $this->entry('soil', '2026-04-10 09:00:00', 30),
        ]));

        $this->assertTrue($result['available']);
        $this->assertSame(1, $result['late_events']);
        $this->assertSame(3, $result['late_days']);
    }
}
