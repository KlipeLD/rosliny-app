<?php

declare(strict_types=1);

namespace App\Services\Plants;

use App\Models\Plant;
use App\Models\PlantEntry;
use Illuminate\Support\Collection;

final readonly class LastWateringService
{
    public function __construct(private WateringEventDetector $eventDetector) {}

    public function details(Plant $plant, ?Collection $entries = null): ?array
    {
        $entries ??= $this->entriesFor($plant);

        return $this->eventDetector->detect($plant, $entries)->last();
    }

    public function detailsForEntry(
        Plant $plant,
        PlantEntry $entry,
        ?Collection $entries = null,
        ?PlantEntry $latestEntry = null,
    ): ?array {
        $entries ??= $this->entriesFor($plant);
        $entryDate = $entry->recorded_at ?? $entry->created_at;

        if (! $entryDate) {
            return $this->details($plant, $entries);
        }

        $wateringEvents = $this->eventDetector->detect($plant, $entries);

        if ($latestEntry && $entry->is($latestEntry) && $entryDate->isToday()) {
            return $wateringEvents
                ->filter(fn (array $event) => $event['at']->lt($entryDate->copy()->startOfDay()))
                ->last();
        }

        return $wateringEvents->last();
    }

    private function entriesFor(Plant $plant): Collection
    {
        return $plant->entries()
            ->orderByRaw('COALESCE(recorded_at, created_at) asc')
            ->get();
    }
}
