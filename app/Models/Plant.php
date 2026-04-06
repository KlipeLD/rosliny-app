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
    ];

    protected $casts = [
        'soil_moisture_min' => 'float',
        'soil_moisture_max' => 'float',
        'soil_moisture_ideal_min' => 'float',
        'soil_moisture_ideal_max' => 'float',
        'watering_interval_days' => 'integer',
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

    public function predictedWatering(?Collection $entries = null): array
    {
        $entries ??= $this->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();

        $wateringMoments = $this->plant_type === 'manual'
            ? $this->manualWateringMoments($entries)
            : $this->inferWateringMoments($entries);

        if ($this->watering_interval_days) {
            $lastWateringAt = $wateringMoments->last();

            if (! $lastWateringAt) {
                return [
                    'available' => false,
                    'reason' => 'Brak wystarczających danych',
                ];
            }

            return [
                'available' => true,
                'mode' => 'calendar',
                'date' => $lastWateringAt->copy()->addDays($this->watering_interval_days),
                'details' => 'Co '.$this->watering_interval_days.' dni od ostatniego zejścia poniżej zakresu idealnego',
                'last_watering_at' => $lastWateringAt,
            ];
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

        return [
            'available' => true,
            'mode' => 'estimated',
            'date' => $lastWateringAt->copy()->addSeconds($averageInterval),
            'details' => 'Estymacja z poprzednich spadków wilgotności poniżej zakresu idealnego',
            'last_watering_at' => $lastWateringAt,
            'samples' => count($intervalsInSeconds),
        ];
    }

    private function inferWateringMoments(Collection $entries): Collection
    {
        $idealMin = $this->soil_moisture_ideal_min ?? 20;
        $moments = collect();

        foreach ($entries as $entry) {
            if ($entry->moist_pct === null) {
                continue;
            }

            $currentMoisture = (float) $entry->moist_pct;
            $entryDate = $entry->recorded_at ?? $entry->created_at;

            // Zgodnie z założeniem aplikacji: jeśli pomiar jest poniżej idealnego minimum,
            // to traktujemy ten moment jako podlewanie.
            if ($currentMoisture < $idealMin) {
                $moments->push($entryDate->copy());
            }
        }

        return $moments;
    }

    private function manualWateringMoments(Collection $entries): Collection
    {
        return $entries
            ->filter(fn ($entry) => $entry->source === 'watering')
            ->map(fn ($entry) => ($entry->recorded_at ?? $entry->created_at)?->copy())
            ->filter()
            ->values();
    }

}
