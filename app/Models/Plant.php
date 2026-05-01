<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Plant extends Model
{
    protected $fillable = [
        'name',
        'description',
        'plant_type',
        'photo_path',
        'soil_moisture_min',
        'soil_moisture_max',
        'soil_moisture_ideal_min',
        'soil_moisture_ideal_max',
        'watering_interval_days',
        'temp_ideal_min',
        'temp_ideal_max',
        'ph_ideal_min',
        'ph_ideal_max',
        'ec_ideal_min',
        'ec_ideal_max',
        'n_ideal_min',
        'n_ideal_max',
        'p_ideal_min',
        'p_ideal_max',
        'k_ideal_min',
        'k_ideal_max',
        'salt_ideal_min',
        'salt_ideal_max',
    ];

    protected $casts = [
        'soil_moisture_min' => 'float',
        'soil_moisture_max' => 'float',
        'soil_moisture_ideal_min' => 'float',
        'soil_moisture_ideal_max' => 'float',
        'watering_interval_days' => 'integer',
        'temp_ideal_min' => 'float',
        'temp_ideal_max' => 'float',
        'ph_ideal_min' => 'float',
        'ph_ideal_max' => 'float',
        'ec_ideal_min' => 'float',
        'ec_ideal_max' => 'float',
        'n_ideal_min' => 'float',
        'n_ideal_max' => 'float',
        'p_ideal_min' => 'float',
        'p_ideal_max' => 'float',
        'k_ideal_min' => 'float',
        'k_ideal_max' => 'float',
        'salt_ideal_min' => 'float',
        'salt_ideal_max' => 'float',
    ];

    public function entries()
    {
        return $this->hasMany(PlantEntry::class);
    }

    public function moistureRanges(): array
    {
        return [
            $this->soil_moisture_ideal_min ?? 20,
            $this->soil_moisture_ideal_max ?? 60,
            $this->soil_moisture_min ?? 10,
            $this->soil_moisture_max ?? 80,
        ];
    }

    public function temperatureRanges(): array
    {
        return $this->rangesWithIdeal('temp', [18, 26, 15, 30]);
    }

    public function phRanges(): array
    {
        return $this->rangesWithIdeal('ph', [6.0, 7.2, 5.5, 7.8]);
    }

    public function ecRanges(): array
    {
        return $this->rangesWithIdeal('ec', [200, 1200, 100, 2000]);
    }

    public function nitrogenRanges(): array
    {
        return $this->rangesWithIdeal('n', [20, 60, 10, 100]);
    }

    public function phosphorusRanges(): array
    {
        return $this->rangesWithIdeal('p', [10, 40, 5, 80]);
    }

    public function potassiumRanges(): array
    {
        return $this->rangesWithIdeal('k', [20, 80, 10, 150]);
    }

    public function saltRanges(): array
    {
        return $this->rangesWithIdeal('salt', [0, 200, 200, 400]);
    }

    private function rangesWithIdeal(string $prefix, array $defaults): array
    {
        $idealMin = $this->{$prefix.'_ideal_min'};
        $idealMax = $this->{$prefix.'_ideal_max'};

        if ($idealMin === null || $idealMax === null) {
            return $defaults;
        }

        return [(float) $idealMin, (float) $idealMax, $defaults[2], $defaults[3]];
    }

    public function predictedWatering(?Collection $entries = null): array
    {
        $entries ??= $this->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();

        $wateringEvents = $this->wateringEvents($entries);
        $wateringMoments = $wateringEvents
            ->pluck('at')
            ->filter()
            ->values();

        if ($this->watering_interval_days) {
            $lastWateringAt = $wateringMoments->last();

            if (! $lastWateringAt) {
                return [
                    'available' => false,
                    'reason' => 'Brak wystarczających danych',
                ];
            }

            return $this->applyMeasurementBasedDelay([
                'available' => true,
                'mode' => 'calendar',
                'date' => $lastWateringAt->copy()->addDays($this->watering_interval_days),
                'details' => 'Co '.$this->watering_interval_days.' dni od ostatniego zejścia poniżej zakresu idealnego',
                'last_watering_at' => $lastWateringAt,
            ], $entries);
        }

        if ($wateringMoments->count() < 2) {
            return [
                'available' => false,
                'reason' => 'Brak wystarczających danych',
            ];
        }

        $intervalsInSeconds = [];
        for ($i = 1; $i < $wateringMoments->count(); $i++) {
            $intervalsInSeconds[] = $wateringMoments[$i - 1]->diffInSeconds($wateringMoments[$i]);
        }

        $averageInterval = (int) round(array_sum($intervalsInSeconds) / count($intervalsInSeconds));
        $lastWateringAt = $wateringMoments->last();

        return $this->applyMeasurementBasedDelay([
            'available' => true,
            'mode' => 'estimated',
            'date' => $lastWateringAt->copy()->addSeconds($averageInterval),
            'details' => 'Estymacja z poprzednich spadków wilgotności poniżej zakresu idealnego',
            'last_watering_at' => $lastWateringAt,
            'samples' => count($intervalsInSeconds),
        ], $entries);
    }

    private function applyMeasurementBasedDelay(array $prediction, Collection $entries): array
    {
        if (
            $this->plant_type !== 'sensor' ||
            ! ($prediction['available'] ?? false) ||
            ! isset($prediction['date'])
        ) {
            return $prediction;
        }

        $latestMeasurement = $this->latestSoilMeasurement($entries);

        if (! $latestMeasurement || $latestMeasurement->moist_pct === null) {
            return $prediction;
        }

        $measurementDate = $latestMeasurement->recorded_at ?? $latestMeasurement->created_at;
        $idealMin = $this->soil_moisture_ideal_min ?? 20;

        if (
            ! $measurementDate ||
            (float) $latestMeasurement->moist_pct < (float) $idealMin ||
            $measurementDate->copy()->startOfDay()->lessThan($prediction['date']->copy()->startOfDay())
        ) {
            return $prediction;
        }

        $prediction['date'] = $measurementDate->copy()->addDay();

        return $prediction;
    }

    private function inferWateringMoments(Collection $entries): Collection
    {
        return $this->sensorWateringEvents($entries)
            ->pluck('at')
            ->filter()
            ->values();
    }

    private function manualWateringMoments(Collection $entries): Collection
    {
        return $this->manualWateringEvents($entries)
            ->pluck('at')
            ->filter()
            ->values();
    }

    public function lastWateringDetails(?Collection $entries = null): ?array
    {
        $entries ??= $this->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();

        return $this->wateringEvents($entries)->last();
    }

    public function wateringInstability(?Collection $entries = null): array
    {
        $entries ??= $this->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();

        $periodDays = 60;
        $periodStart = now()->subDays($periodDays);
        $entries = $entries
            ->filter(fn ($entry) => ($entry->recorded_at ?? $entry->created_at)?->greaterThanOrEqualTo($periodStart))
            ->values();

        $wateringEvents = $this->wateringEvents($entries);

        if ($wateringEvents->isEmpty()) {
            return [
                'available' => false,
                'reason' => 'Brak podlewań do oceny',
                'period_days' => $periodDays,
            ];
        }

        $criticalMin = $this->soil_moisture_min ?? 10;
        $criticalDryEvents = $wateringEvents
            ->filter(fn ($event) => $event['moist_pct'] !== null && (float) $event['moist_pct'] < (float) $criticalMin)
            ->count();

        $earlyEvents = 0;

        if ($this->watering_interval_days && $wateringEvents->count() > 1) {
            for ($i = 1; $i < $wateringEvents->count(); $i++) {
                $expectedAt = $wateringEvents[$i - 1]['at']->copy()->addDays($this->watering_interval_days);

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
                'period_days' => $periodDays,
            ];
        }

        $totalEvents = $wateringEvents->count();

        return [
            'available' => true,
            'score' => $score,
            'period_days' => $periodDays,
            'total_events' => $totalEvents,
            'critical_dry_events' => $criticalDryEvents,
            'critical_dry_percent' => (int) round(($criticalDryEvents / $totalEvents) * 100),
            'early_events' => $earlyEvents,
            'last_event_at' => $wateringEvents->last()['at'],
        ];
    }

    public function waterRetention(?Collection $entries = null): array
    {
        $entries ??= $this->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();

        $periodDays = 60;
        $periodStart = now()->subDays($periodDays);

        $wateringEvents = $this->wateringEvents($entries);

        if (! $this->watering_interval_days || $wateringEvents->count() < 2) {
            return [
                'available' => false,
                'reason' => 'Brak interwału albo zbyt mało podlewań do oceny',
                'period_days' => $periodDays,
            ];
        }

        $lateEvents = 0;
        $lateDays = 0;
        $idealMin = $this->soil_moisture_ideal_min ?? 20;

        for ($i = 1; $i < $wateringEvents->count(); $i++) {
            $expectedAt = $wateringEvents[$i - 1]['at']->copy()->addDays($this->watering_interval_days)->startOfDay();
            $actualAt = $wateringEvents[$i]['at']->copy()->startOfDay();

            if (
                $actualAt->greaterThanOrEqualTo($periodStart) &&
                $actualAt->greaterThan($expectedAt) &&
                $this->hasNotDryEnoughMeasurementForWatering($wateringEvents[$i], $entries, $idealMin, $expectedAt)
            ) {
                $lateEvents++;
                $lateDays += (int) $expectedAt->diffInDays($actualAt);
            }
        }

        if ($lateEvents === 0) {
            return [
                'available' => false,
                'reason' => 'Brak podlewań po przewidywanym terminie',
                'period_days' => $periodDays,
            ];
        }

        return [
            'available' => true,
            'score' => $lateDays,
            'period_days' => $periodDays,
            'total_events' => $wateringEvents->count(),
            'late_events' => $lateEvents,
            'late_days' => $lateDays,
            'average_late_days' => round($lateDays / $lateEvents, 1),
            'last_event_at' => $wateringEvents->last()['at'],
        ];
    }

    private function hasNotDryEnoughMeasurementForWatering(array $wateringEvent, Collection $entries, float|int $idealMin, $expectedAt): bool
    {
        if ($wateringEvent['source'] === 'watering' && $wateringEvent['moist_pct'] !== null) {
            return (float) $wateringEvent['moist_pct'] >= (float) $idealMin;
        }

        $wateringAt = $wateringEvent['at'];
        $sameDayMeasurement = $entries
            ->filter(fn ($entry) => $entry->source === 'soil' && $entry->moist_pct !== null)
            ->filter(fn ($entry) => ($entry->recorded_at ?? $entry->created_at)?->isSameDay($wateringAt))
            ->sortBy(fn ($entry) => abs(($entry->recorded_at ?? $entry->created_at)->timestamp - $wateringAt->timestamp))
            ->first();

        if ($sameDayMeasurement) {
            if ((float) $sameDayMeasurement->moist_pct >= (float) $idealMin) {
                return true;
            }
        }

        $nextMeasurement = $entries
            ->filter(fn ($entry) => $entry->source === 'soil' && $entry->moist_pct !== null)
            ->filter(function ($entry) use ($expectedAt) {
                $entryDate = $entry->recorded_at ?? $entry->created_at;

                return $entryDate && $entryDate->copy()->startOfDay()->greaterThanOrEqualTo($expectedAt);
            })
            ->sortBy(fn ($entry) => ($entry->recorded_at ?? $entry->created_at)->timestamp)
            ->first();

        return $nextMeasurement && (float) $nextMeasurement->moist_pct >= (float) $idealMin;
    }

    public function copyLastWateringDetailsForEntry(PlantEntry $entry, ?Collection $entries = null, ?PlantEntry $latestEntry = null): ?array
    {
        $entries ??= $this->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();

        $entryDate = $entry->recorded_at ?? $entry->created_at;

        if (! $entryDate) {
            return $this->lastWateringDetails($entries);
        }

        $wateringEvents = $this->wateringEvents($entries);

        if ($latestEntry && $entry->is($latestEntry) && $entryDate->isToday()) {
            return $wateringEvents
                ->filter(fn ($event) => $event['at']->lt($entryDate->copy()->startOfDay()))
                ->last();
        }

        return $wateringEvents->last();
    }

    private function wateringEvents(Collection $entries): Collection
    {
        if ($this->plant_type === 'manual') {
            return $this->manualWateringEvents($entries);
        }

        return $this->sensorWateringEvents($entries)
            ->merge($this->manualWateringEvents($entries))
            ->sortBy(fn ($event) => $event['at']->timestamp)
            ->values();
    }

    private function sensorWateringEvents(Collection $entries): Collection
    {
        $idealMin = $this->soil_moisture_ideal_min ?? 20;
        $events = collect();

        foreach ($entries as $entry) {
            if ($entry->moist_pct === null) {
                continue;
            }

            $currentMoisture = (float) $entry->moist_pct;
            $entryDate = $entry->recorded_at ?? $entry->created_at;

            // Zgodnie z założeniem aplikacji: jeśli pomiar jest poniżej idealnego minimum,
            // to traktujemy ten moment jako podlewanie.
            if ($entryDate && $currentMoisture < $idealMin) {
                $events->push([
                    'at' => $entryDate->copy(),
                    'moist_pct' => (float) $entry->moist_pct,
                    'source' => $entry->source,
                ]);
            }
        }

        return $events->values();
    }

    private function manualWateringEvents(Collection $entries): Collection
    {
        return $entries
            ->filter(fn ($entry) => $entry->source === 'watering')
            ->map(function ($entry) {
                $entryDate = $entry->recorded_at ?? $entry->created_at;

                if (! $entryDate) {
                    return null;
                }

                return [
                    'at' => $entryDate->copy(),
                    'moist_pct' => $entry->moist_pct !== null ? (float) $entry->moist_pct : null,
                    'source' => $entry->source,
                ];
            })
            ->filter()
            ->values();
    }

    private function latestSoilMeasurement(Collection $entries): ?PlantEntry
    {
        return $entries
            ->filter(fn ($entry) => $entry->source === 'soil' && $entry->moist_pct !== null)
            ->sortBy(fn ($entry) => ($entry->recorded_at ?? $entry->created_at)?->timestamp ?? 0)
            ->last();
    }

}
