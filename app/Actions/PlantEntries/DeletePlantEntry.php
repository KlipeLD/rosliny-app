<?php

declare(strict_types=1);

namespace App\Actions\PlantEntries;

use App\Models\PlantEntry;
use App\Services\PhotoStorage;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final readonly class DeletePlantEntry
{
    public function __construct(
        private PhotoStorage $photoStorage,
    ) {}

    public function execute(PlantEntry $entry): void
    {
        $photoPath = $entry->current_photo_path;

        DB::transaction(function () use ($entry): void {
            if (! $entry->delete()) {
                throw new RuntimeException(
                    'The plant entry could not be deleted.',
                );
            }
        });

        $this->photoStorage->delete($photoPath);
    }
}
