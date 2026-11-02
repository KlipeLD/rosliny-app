<?php

declare(strict_types=1);

namespace App\Services\Plants;

use App\Enums\PlantEntrySource;
use App\Enums\PlantType;
use App\Models\Plant;
use App\Models\PlantEntry;
use Illuminate\Support\Collection;

final readonly class WateringPredictionService
{
    public function __construct(private WateringEventDetector $eventDetector) {}

    public function predict(Plant $plant, ?Collection $entries = null): array
    {
        $entries ??= $this->entriesFor($plant);
        $wateringMoments = $this->eventDetector
            ->detect($plant, $entries)
            ->pluck('at')
            ->filter()
            ->values();

        if ($plant->watering_interval_days) {
            $lastWateringAt = $wateringMoments->last();

            if (! $lastWateringAt) {
                return $this->unavailable();
            }

            return $this->applyMeasurementBasedDelay($plant, [
                'available' => true,
                'mode' => 'calendar',
                'date' => $lastWateringAt->copy()->addDays($plant->watering_interval_days),
                'details' => 'Co '.$plant->watering_interval_days.' dni od ostatniego zejścia poniżej zakresu idealnego',
                'last_watering_at' => $lastWateringAt,
            ], $entries);
        }

        if ($wateringMoments->count() < 2) {
            return $this->unavailable();
        }

        $intervalsInSeconds = [];
        for ($i = 1; $i < $wateringMoments->count(); $i++) {
            $intervalsInSeconds[] = $wateringMoments[$i - 1]->diffInSeconds($wateringMoments[$i]);
        }

        $averageInterval = (int) round(array_sum($intervalsInSeconds) / count($intervalsInSeconds));
        $lastWateringAt = $wateringMoments->last();

        return $this->applyMeasurementBasedDelay($plant, [
            'available' => true,
            'mode' => 'estimated',
            'date' => $lastWateringAt->copy()->addSeconds($averageInterval),
            'details' => 'Estymacja z poprzednich spadków wilgotności poniżej zakresu idealnego',
            'last_watering_at' => $lastWateringAt,
            'samples' => count($intervalsInSeconds),
        ], $entries);
    }

    private function applyMeasurementBasedDelay(Plant $plant, array $prediction, Collection $entries): array
    {
        if ($plant->plant_type !== PlantType::Sensor) {
            return $prediction;
        }

        $latestMeasurement = $this->latestSoilMeasurement($entries);

        if (! $latestMeasurement || $latestMeasurement->moist_pct === null) {
            return $prediction;
        }

        $measurementDate = $latestMeasurement->recorded_at ?? $latestMeasurement->created_at;
        $idealMin = $plant->soil_moisture_ideal_min ?? 20;

        if (
            ! $measurementDate
            || (float) $latestMeasurement->moist_pct < (float) $idealMin
            || $measurementDate->copy()->startOfDay()->lessThan($prediction['date']->copy()->startOfDay())
        ) {
            return $prediction;
        }

        $prediction['date'] = $measurementDate->copy()->addDay();

        return $prediction;
    }

    private function latestSoilMeasurement(Collection $entries): ?PlantEntry
    {
        return $entries
            ->filter(fn ($entry) => $entry->source === PlantEntrySource::Soil && $entry->moist_pct !== null)
            ->sortBy(fn ($entry) => ($entry->recorded_at ?? $entry->created_at)->timestamp)
            ->last();
    }

    private function entriesFor(Plant $plant): Collection
    {
        return $plant->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();
    }

    private function unavailable(): array
    {
        return [
            'available' => false,
            'reason' => 'Brak wystarczających danych',
        ];
    }
}
