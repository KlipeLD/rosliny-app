<?php

declare(strict_types=1);

namespace App\Actions\Plants;

use App\Models\Plant;
use App\Services\PhotoStorage;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final readonly class DeletePlant
{
    public function __construct(
        private PhotoStorage $photoStorage,
    ) {}

    public function execute(Plant $plant): void
    {
        $plantPhotoPath = $plant->photo_path;

        $entryPhotoPaths = $plant->entries()
            ->whereNotNull('current_photo_path')
            ->pluck('current_photo_path')
            ->all();

        DB::transaction(function () use ($plant): void {
            if (! $plant->delete()) {
                throw new RuntimeException(
                    'The plant could not be deleted.',
                );
            }
        });

        $this->photoStorage->delete($plantPhotoPath);
        $this->photoStorage->deleteMany($entryPhotoPaths);
    }
}
