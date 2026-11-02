<?php

declare(strict_types=1);

namespace App\Services\Plants;

use App\Enums\PlantEntrySource;
use App\Enums\PlantType;
use App\Models\Plant;
use Illuminate\Support\Collection;

final class WateringEventDetector
{
    public function detect(Plant $plant, Collection $entries): Collection
    {
        // Analizy zwracają tablice, dlatego nie mogą korzystać
        // z kolekcji Eloquent oczekującej modeli.
        $entries = $entries->toBase();

        if ($plant->plant_type === PlantType::Manual) {
            return $this->manualEvents($entries);
        }

        return $this->sensorEvents($plant, $entries)
            ->merge($this->manualEvents($entries))
            ->sortBy(fn (array $event) => $event['at']->timestamp)
            ->values();
    }

    private function sensorEvents(Plant $plant, Collection $entries): Collection
    {
        $idealMin = $plant->soil_moisture_ideal_min ?? 20;

        return $entries
            ->filter(fn ($entry) => $entry->moist_pct !== null)
            ->map(function ($entry) use ($idealMin): ?array {
                $entryDate = $entry->recorded_at ?? $entry->created_at;

                if (! $entryDate || (float) $entry->moist_pct >= (float) $idealMin) {
                    return null;
                }

                return [
                    'at' => $entryDate->copy(),
                    'moist_pct' => (float) $entry->moist_pct,
                    'source' => $entry->source,
                ];
            })
            ->filter()
            ->values();
    }

    private function manualEvents(Collection $entries): Collection
    {
        return $entries
            ->filter(fn ($entry) => $entry->source === PlantEntrySource::Watering)
            ->map(function ($entry): ?array {
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
}
