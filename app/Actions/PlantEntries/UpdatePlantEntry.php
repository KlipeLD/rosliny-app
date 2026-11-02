<?php

declare(strict_types=1);

namespace App\Actions\PlantEntries;

use App\Models\PlantEntry;
use App\Services\PhotoStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class UpdatePlantEntry
{
    public function __construct(
        private PhotoStorage $photoStorage,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(
        PlantEntry $entry,
        array $data,
        ?UploadedFile $photo = null,
        bool $removePhoto = false,
    ): PlantEntry {
        unset($data['current_photo'], $data['remove_current_photo']);

        $oldPhotoPath = $entry->current_photo_path;
        $newPhotoPath = null;
        $targetPhotoPath = $oldPhotoPath;

        if ($photo !== null) {
            $newPhotoPath = $this->photoStorage->store(
                $photo,
                'plant-entries',
            );

            $targetPhotoPath = $newPhotoPath;
        } elseif ($removePhoto) {
            $targetPhotoPath = null;
        }

        try {
            DB::transaction(function () use (
                $entry,
                $data,
                $targetPhotoPath,
            ): void {
                $entry->fill($data);
                $entry->current_photo_path = $targetPhotoPath;

                if (! $entry->save()) {
                    throw new RuntimeException(
                        'The plant entry could not be updated.',
                    );
                }
            });
        } catch (Throwable $exception) {
            $this->photoStorage->delete($newPhotoPath);
            $entry->current_photo_path = $oldPhotoPath;

            throw $exception;
        }

        if ($targetPhotoPath !== $oldPhotoPath) {
            $this->photoStorage->delete($oldPhotoPath);
        }

        return $entry;
    }
}
