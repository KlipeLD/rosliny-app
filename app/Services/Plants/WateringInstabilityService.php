<?php

declare(strict_types=1);

namespace App\Services\Plants;

use App\Models\Plant;
use Illuminate\Support\Collection;

final readonly class WateringInstabilityService
{
    private const PERIOD_DAYS = 60;

    public function __construct(private WateringEventDetector $eventDetector) {}

    public function analyze(Plant $plant, ?Collection $entries = null): array
    {
        $entries ??= $this->entriesFor($plant);
        $periodStart = now()->subDays(self::PERIOD_DAYS);
        $entries = $entries
            ->filter(fn ($entry) => ($entry->recorded_at ?? $entry->created_at)?->greaterThanOrEqualTo($periodStart))
            ->values();

        $wateringEvents = $this->eventDetector->detect($plant, $entries);

        if ($wateringEvents->isEmpty()) {
            return [
                'available' => false,
                'reason' => 'Brak podlewań do oceny',
                'period_days' => self::PERIOD_DAYS,
            ];
        }

        $criticalMin = $plant->soil_moisture_min ?? 10;
        $criticalDryEvents = $wateringEvents
            ->filter(fn (array $event) => $event['moist_pct'] !== null && (float) $event['moist_pct'] < (float) $criticalMin)
            ->count();

        $earlyEvents = 0;

        if ($plant->watering_interval_days && $wateringEvents->count() > 1) {
            for ($i = 1; $i < $wateringEvents->count(); $i++) {
                $expectedAt = $wateringEvents[$i - 1]['at']->copy()->addDays($plant->watering_interval_days);

                if ($wateringEvents[$i]['at']->copy()->startOfDay()->lessThan($expectedAt->copy()->startOfDay())) {
                    $earlyEvents++;
                }
            }
        }

        $score = ($criticalDryEvents * 2) + $earlyEvents;

        if ($score === 0) {
            return [
                'available' => false,
                'reason' => 'Brak oznak niestabilności',
                'period_days' => self::PERIOD_DAYS,
            ];
        }

        $totalEvents = $wateringEvents->count();

        return [
            'available' => true,
            'score' => $score,
            'period_days' => self::PERIOD_DAYS,
            'total_events' => $totalEvents,
            'critical_dry_events' => $criticalDryEvents,
            'critical_dry_percent' => (int) round(($criticalDryEvents / $totalEvents) * 100),
            'early_events' => $earlyEvents,
            'last_event_at' => $wateringEvents->last()['at'],
        ];
    }

    private function entriesFor(Plant $plant): Collection
    {
        return $plant->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();
    }
}
