<?php

declare(strict_types=1);

namespace Tests\Unit\Plants;

use App\Enums\PlantEntrySource;
use App\Models\Plant;
use App\Models\PlantEntry;
use App\Services\Plants\LastWateringService;
use App\Services\Plants\WateringEventDetector;
use Carbon\Carbon;
use Tests\TestCase;

final class LastWateringDetailsTest extends TestCase
{
    private LastWateringService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LastWateringService(new WateringEventDetector);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_result_is_null_without_watering_events(): void
    {
        $plant = $this->manualPlant();

        $this->assertNull(
            $this->service->details($plant, collect()),
        );
    }

    public function test_last_manual_watering_is_returned(): void
    {
        $plant = $this->manualPlant();

        $entries = collect([
            $this->entry('watering', '2026-04-01 09:00:00'),
            $this->entry('watering', '2026-04-05 12:00:00'),
        ]);

        $result = $this->service->details($plant, $entries);

        $this->assertNotNull($result);
        $this->assertSame(PlantEntrySource::Watering, $result['source']);
        $this->assertNull($result['moist_pct']);
        $this->assertSame(
            '2026-04-05 12:00:00',
            $result['at']->format('Y-m-d H:i:s'),
        );
    }

    public function test_sensor_plant_combines_sensor_and_manual_events(): void
    {
        $plant = new Plant([
            'plant_type' => 'sensor',
            'soil_moisture_ideal_min' => 20,
        ]);

        $entries = collect([
            $this->entry('watering', '2026-04-05 09:00:00'),
            $this->entry('soil', '2026-04-06 09:00:00', 8),
        ]);

        $result = $this->service->details($plant, $entries);

        $this->assertNotNull($result);
        $this->assertSame(PlantEntrySource::Soil, $result['source']);
        $this->assertSame(8.0, $result['moist_pct']);
        $this->assertSame(
            '2026-04-06 09:00:00',
            $result['at']->format('Y-m-d H:i:s'),
        );
    }

    public function test_manual_plant_ignores_soil_measurements(): void
    {
        $plant = $this->manualPlant();

        $entries = collect([
            $this->entry('watering', '2026-04-05 09:00:00'),
            $this->entry('soil', '2026-04-06 09:00:00', 5),
        ]);

        $result = $this->service->details($plant, $entries);

        $this->assertNotNull($result);
        $this->assertSame(PlantEntrySource::Watering, $result['source']);
        $this->assertSame(
            '2026-04-05 09:00:00',
            $result['at']->format('Y-m-d H:i:s'),
        );
    }

    public function test_latest_entry_from_today_uses_previous_watering(): void
    {
        Carbon::setTestNow('2026-05-01 12:00:00');

        $plant = $this->manualPlant();

        $previous = $this->entry(
            'watering',
            '2026-04-28 09:00:00',
            id: 1,
        );

        $today = $this->entry(
            'watering',
            '2026-05-01 10:00:00',
            id: 2,
        );

        $result = $this->service->detailsForEntry(
            $plant,
            $today,
            collect([$previous, $today]),
            $today,
        );

        $this->assertNotNull($result);
        $this->assertSame(
            '2026-04-28 09:00:00',
            $result['at']->format('Y-m-d H:i:s'),
        );
    }

    public function test_entry_without_date_uses_last_watering(): void
    {
        $plant = $this->manualPlant();

        $watering = $this->entry(
            'watering',
            '2026-04-28 09:00:00',
            id: 1,
        );

        $entryWithoutDate = new PlantEntry([
            'source' => 'soil',
        ]);
        $entryWithoutDate->id = 2;

        $result = $this->service->detailsForEntry(
            $plant,
            $entryWithoutDate,
            collect([$watering]),
        );

        $this->assertNotNull($result);
        $this->assertSame(
            '2026-04-28 09:00:00',
            $result['at']->format('Y-m-d H:i:s'),
        );
    }

    private function manualPlant(): Plant
    {
        return new Plant([
            'plant_type' => 'manual',
        ]);
    }

    private function entry(
        string $source,
        ?string $date,
        ?float $moisture = null,
        ?int $id = null,
    ): PlantEntry {
        $entry = new PlantEntry([
            'source' => $source,
            'moist_pct' => $moisture,
            'recorded_at' => $date !== null
                ? Carbon::parse($date)
                : null,
        ]);

        if ($id !== null) {
            $entry->id = $id;
        }

        return $entry;
    }
}
