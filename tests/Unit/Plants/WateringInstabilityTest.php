<?php

declare(strict_types=1);

namespace Tests\Unit\Plants;

use App\Models\Plant;
use App\Models\PlantEntry;
use App\Services\Plants\WateringEventDetector;
use App\Services\Plants\WateringInstabilityService;
use Carbon\Carbon;
use Tests\TestCase;

final class WateringInstabilityTest extends TestCase
{
    private WateringInstabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new WateringInstabilityService(new WateringEventDetector);
        Carbon::setTestNow('2026-05-01 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_result_is_unavailable_without_events(): void
    {
        $result = $this->service->analyze($this->sensorPlant(), collect());

        $this->assertFalse($result['available']);
        $this->assertSame(60, $result['period_days']);
    }

    public function test_result_is_unavailable_without_instability(): void
    {
        $plant = $this->manualPlant();

        $result = $this->service->analyze($plant, collect([
            $this->entry('watering', '2026-04-01 09:00:00'),
            $this->entry('watering', '2026-04-06 09:00:00'),
        ]));

        $this->assertFalse($result['available']);
        $this->assertSame(
            'Brak oznak niestabilności',
            $result['reason'],
        );
    }

    public function test_event_older_than_sixty_days_is_ignored(): void
    {
        $result = $this->service->analyze($this->sensorPlant(), collect([
            $this->entry('soil', '2026-02-01 09:00:00', 5),
        ]));

        $this->assertFalse($result['available']);
        $this->assertSame('Brak podlewań do oceny', $result['reason']);
    }

    public function test_critical_dry_event_adds_two_points(): void
    {
        $result = $this->service->analyze($this->sensorPlant(), collect([
            $this->entry('soil', '2026-04-10 09:00:00', 8),
        ]));

        $this->assertTrue($result['available']);
        $this->assertSame(1, $result['critical_dry_events']);
        $this->assertSame(0, $result['early_events']);
        $this->assertSame(2, $result['score']);
        $this->assertSame(100, $result['critical_dry_percent']);
    }

    public function test_early_event_adds_one_point(): void
    {
        $result = $this->service->analyze($this->manualPlant(), collect([
            $this->entry('watering', '2026-04-01 09:00:00'),
            $this->entry('watering', '2026-04-04 09:00:00'),
        ]));

        $this->assertTrue($result['available']);
        $this->assertSame(0, $result['critical_dry_events']);
        $this->assertSame(1, $result['early_events']);
        $this->assertSame(1, $result['score']);
    }

    public function test_value_equal_to_critical_minimum_is_not_critical(): void
    {
        $result = $this->service->analyze($this->sensorPlant(), collect([
            $this->entry('soil', '2026-04-10 09:00:00', 10),
        ]));

        $this->assertFalse($result['available']);
        $this->assertSame(
            'Brak oznak niestabilności',
            $result['reason'],
        );
    }

    public function test_critical_and_early_events_are_combined(): void
    {
        $result = $this->service->analyze($this->sensorPlant(), collect([
            $this->entry('soil', '2026-02-15 09:00:00', 8),
            $this->entry('soil', '2026-04-01 09:00:00', 8),
            $this->entry('soil', '2026-04-04 09:00:00', 15),
            $this->entry('watering', '2026-04-08 09:00:00'),
        ]));

        $this->assertTrue($result['available']);
        $this->assertSame(1, $result['critical_dry_events']);
        $this->assertSame(2, $result['early_events']);
        $this->assertSame(4, $result['score']);
        $this->assertSame(33, $result['critical_dry_percent']);
        $this->assertSame(3, $result['total_events']);
    }

    private function sensorPlant(): Plant
    {
        return new Plant([
            'plant_type' => 'sensor',
            'soil_moisture_min' => 10,
            'soil_moisture_ideal_min' => 20,
            'watering_interval_days' => 5,
        ]);
    }

    private function manualPlant(): Plant
    {
        return new Plant([
            'plant_type' => 'manual',
            'watering_interval_days' => 5,
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
}
