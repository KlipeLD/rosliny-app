<?php

declare(strict_types=1);

namespace App\Services\Plants;

use App\Enums\PlantEntrySource;
use App\Models\Plant;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final readonly class WaterRetentionService
{
    private const PERIOD_DAYS = 60;

    public function __construct(private WateringEventDetector $eventDetector) {}

    public function analyze(Plant $plant, ?Collection $entries = null): array
    {
        $entries ??= $this->entriesFor($plant);
        $periodStart = now()->subDays(self::PERIOD_DAYS);
        $wateringEvents = $this->eventDetector->detect($plant, $entries);

        if (! $plant->watering_interval_days || $wateringEvents->count() < 2) {
            return [
                'available' => false,
                'reason' => 'Brak interwału albo zbyt mało podlewań do oceny',
                'period_days' => self::PERIOD_DAYS,
            ];
        }

        $lateEvents = 0;
        $lateDays = 0;
        $idealMin = $plant->soil_moisture_ideal_min ?? 20;

        for ($i = 1; $i < $wateringEvents->count(); $i++) {
            $expectedAt = $wateringEvents[$i - 1]['at']->copy()->addDays($plant->watering_interval_days)->startOfDay();
            $actualAt = $wateringEvents[$i]['at']->copy()->startOfDay();

            if (
                $actualAt->greaterThanOrEqualTo($periodStart)
                && $actualAt->greaterThan($expectedAt)
                && $this->hasNotDryEnoughMeasurementForWatering(
                    $wateringEvents[$i],
                    $entries,
                    $idealMin,
                    $expectedAt,
                )
            ) {
                $lateEvents++;
                $lateDays += (int) $expectedAt->diffInDays($actualAt);
            }
        }

        if ($lateEvents === 0) {
            return [
                'available' => false,
                'reason' => 'Brak podlewań po przewidywanym terminie',
                'period_days' => self::PERIOD_DAYS,
            ];
        }

        return [
            'available' => true,
            'score' => $lateDays,
            'period_days' => self::PERIOD_DAYS,
            'total_events' => $wateringEvents->count(),
            'late_events' => $lateEvents,
            'late_days' => $lateDays,
            'average_late_days' => round($lateDays / $lateEvents, 1),
            'last_event_at' => $wateringEvents->last()['at'],
        ];
    }

    private function hasNotDryEnoughMeasurementForWatering(
        array $wateringEvent,
        Collection $entries,
        float|int $idealMin,
        CarbonInterface $expectedAt,
    ): bool {
        if ($wateringEvent['source'] === PlantEntrySource::Watering && $wateringEvent['moist_pct'] !== null) {
            return (float) $wateringEvent['moist_pct'] >= (float) $idealMin;
        }

        $wateringAt = $wateringEvent['at'];
        $sameDayMeasurement = $entries
            ->filter(fn ($entry) => $entry->source === PlantEntrySource::Soil && $entry->moist_pct !== null)
            ->filter(fn ($entry) => ($entry->recorded_at ?? $entry->created_at)?->isSameDay($wateringAt))
            ->sortBy(fn ($entry) => abs(($entry->recorded_at ?? $entry->created_at)->timestamp - $wateringAt->timestamp))
            ->first();

        if ($sameDayMeasurement && (float) $sameDayMeasurement->moist_pct >= (float) $idealMin) {
            return true;
        }

        $nextMeasurement = $entries
            ->filter(fn ($entry) => $entry->source === PlantEntrySource::Soil && $entry->moist_pct !== null)
            ->filter(function ($entry) use ($expectedAt): bool {
                $entryDate = $entry->recorded_at ?? $entry->created_at;

                return $entryDate && $entryDate->copy()->startOfDay()->greaterThanOrEqualTo($expectedAt);
            })
            ->sortBy(fn ($entry) => ($entry->recorded_at ?? $entry->created_at)->timestamp)
            ->first();

        return $nextMeasurement && (float) $nextMeasurement->moist_pct >= (float) $idealMin;
    }

    private function entriesFor(Plant $plant): Collection
    {
        return $plant->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();
    }
}
