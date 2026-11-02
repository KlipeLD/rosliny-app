<?php

declare(strict_types=1);

namespace App\Actions\Plants;

use App\Models\Plant;
use App\Services\PhotoStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class UpdatePlant
{
    public function __construct(
        private PhotoStorage $photoStorage,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(
        Plant $plant,
        array $data,
        ?UploadedFile $photo = null,
        bool $removePhoto = false,
    ): Plant {
        unset($data['photo'], $data['remove_photo']);

        $oldPhotoPath = $plant->photo_path;
        $newPhotoPath = null;
        $targetPhotoPath = $oldPhotoPath;

        if ($photo !== null) {
            $newPhotoPath = $this->photoStorage->store($photo, 'plants');
            $targetPhotoPath = $newPhotoPath;
        } elseif ($removePhoto) {
            $targetPhotoPath = null;
        }

        try {
            DB::transaction(function () use (
                $plant,
                $data,
                $targetPhotoPath,
            ): void {
                $plant->fill($data);
                $plant->photo_path = $targetPhotoPath;

                if (! $plant->save()) {
                    throw new RuntimeException(
                        'The plant could not be updated.',
                    );
                }
            });
        } catch (Throwable $exception) {
            // Baza nie zapisała nowej ścieżki, więc nowy plik jest zbędny.
            $this->photoStorage->delete($newPhotoPath);

            // Przywracamy poprawny stan obiektu w pamięci.
            $plant->photo_path = $oldPhotoPath;

            throw $exception;
        }

        // Transakcja została zatwierdzona. Stary plik nie jest już potrzebny.
        if ($targetPhotoPath !== $oldPhotoPath) {
            $this->photoStorage->delete($oldPhotoPath);
        }

        return $plant;
    }
}
