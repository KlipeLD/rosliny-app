<?php

namespace Tests\Unit;

use App\Models\Plant;
use App\Models\PlantEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PlantWateringPredictionTest extends TestCase
{
    public function test_ideal_measurement_on_predicted_watering_day_delays_prediction_to_next_day(): void
    {
        $plant = new Plant([
            'plant_type' => 'sensor',
            'soil_moisture_ideal_min' => 20,
            'watering_interval_days' => 2,
        ]);

        $entries = new Collection([
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 8,
                'recorded_at' => Carbon::parse('2026-04-12 09:00:00'),
            ]),
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 35,
                'recorded_at' => Carbon::parse('2026-04-14 18:00:00'),
            ]),
        ]);

        $prediction = $plant->predictedWatering($entries);

        $this->assertTrue($prediction['available']);
        $this->assertSame(
            '2026-04-15 18:00:00',
            $prediction['date']->format('Y-m-d H:i:s')
        );
    }

    public function test_ideal_measurement_after_overdue_watering_day_delays_prediction_to_next_day(): void
    {
        $plant = new Plant([
            'plant_type' => 'sensor',
            'soil_moisture_ideal_min' => 20,
            'watering_interval_days' => 2,
        ]);

        $entries = new Collection([
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 8,
                'recorded_at' => Carbon::parse('2026-04-11 09:00:00'),
            ]),
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 35,
                'recorded_at' => Carbon::parse('2026-04-14 18:00:00'),
            ]),
        ]);

        $prediction = $plant->predictedWatering($entries);

        $this->assertTrue($prediction['available']);
        $this->assertSame(
            '2026-04-15 18:00:00',
            $prediction['date']->format('Y-m-d H:i:s')
        );
    }

    public function test_sensor_plant_uses_manual_watering_entry_for_prediction(): void
    {
        $plant = new Plant([
            'plant_type' => 'sensor',
            'soil_moisture_ideal_min' => 20,
            'watering_interval_days' => 2,
        ]);

        $entries = new Collection([
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 8,
                'recorded_at' => Carbon::parse('2026-04-11 09:00:00'),
            ]),
            new PlantEntry([
                'source' => 'watering',
                'recorded_at' => Carbon::parse('2026-04-14 18:00:00'),
            ]),
        ]);

        $prediction = $plant->predictedWatering($entries);

        $this->assertTrue($prediction['available']);
        $this->assertSame(
            '2026-04-16 18:00:00',
            $prediction['date']->format('Y-m-d H:i:s')
        );
    }

    public function test_watering_instability_counts_critical_dry_and_early_events(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-01 12:00:00'));

        $plant = new Plant([
            'plant_type' => 'sensor',
            'soil_moisture_min' => 10,
            'soil_moisture_ideal_min' => 20,
            'watering_interval_days' => 5,
        ]);

        $entries = new Collection([
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 8,
                'recorded_at' => Carbon::parse('2026-02-15 09:00:00'),
            ]),
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 8,
                'recorded_at' => Carbon::parse('2026-04-01 09:00:00'),
            ]),
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 15,
                'recorded_at' => Carbon::parse('2026-04-04 09:00:00'),
            ]),
            new PlantEntry([
                'source' => 'watering',
                'recorded_at' => Carbon::parse('2026-04-08 09:00:00'),
            ]),
        ]);

        $instability = $plant->wateringInstability($entries);

        $this->assertTrue($instability['available']);
        $this->assertSame(1, $instability['critical_dry_events']);
        $this->assertSame(2, $instability['early_events']);
        $this->assertSame(4, $instability['score']);
        $this->assertSame(33, $instability['critical_dry_percent']);
        $this->assertSame(60, $instability['period_days']);

        Carbon::setTestNow();
    }

    public function test_water_retention_counts_late_events_within_last_60_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-01 12:00:00'));

        $plant = new Plant([
            'plant_type' => 'sensor',
            'soil_moisture_ideal_min' => 20,
            'watering_interval_days' => 5,
        ]);

        $entries = new Collection([
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 8,
                'recorded_at' => Carbon::parse('2026-02-15 09:00:00'),
            ]),
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 8,
                'recorded_at' => Carbon::parse('2026-04-01 09:00:00'),
            ]),
            new PlantEntry([
                'source' => 'watering',
                'recorded_at' => Carbon::parse('2026-04-09 09:00:00'),
            ]),
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 35,
                'recorded_at' => Carbon::parse('2026-04-09 10:00:00'),
            ]),
            new PlantEntry([
                'source' => 'watering',
                'recorded_at' => Carbon::parse('2026-04-17 09:00:00'),
            ]),
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 30,
                'recorded_at' => Carbon::parse('2026-04-18 09:00:00'),
            ]),
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 35,
                'recorded_at' => Carbon::parse('2026-04-22 09:00:00'),
            ]),
            new PlantEntry([
                'source' => 'soil',
                'moist_pct' => 8,
                'recorded_at' => Carbon::parse('2026-04-27 09:00:00'),
            ]),
        ]);

        $retention = $plant->waterRetention($entries);

        $this->assertTrue($retention['available']);
        $this->assertSame(3, $retention['late_events']);
        $this->assertSame(11, $retention['late_days']);
        $this->assertSame(11, $retention['score']);
        $this->assertSame(3.7, $retention['average_late_days']);
        $this->assertSame(60, $retention['period_days']);

        Carbon::setTestNow();
    }
}
