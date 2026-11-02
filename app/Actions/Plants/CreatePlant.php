<?php

declare(strict_types=1);

namespace App\Actions\Plants;

use App\Models\Plant;
use App\Services\PhotoStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreatePlant
{
    public function __construct(
        private PhotoStorage $photoStorage,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(
        array $data,
        ?UploadedFile $photo = null,
    ): Plant {
        unset($data['photo'], $data['remove_photo']);

        $photoPath = null;

        try {
            if ($photo !== null) {
                $photoPath = $this->photoStorage->store($photo, 'plants');
            }

            $data['photo_path'] = $photoPath;

            return DB::transaction(function () use ($data): Plant {
                $plant = new Plant;
                $plant->fill($data);

                if (! $plant->save()) {
                    throw new \RuntimeException('The plant could not be created.');
                }

                return $plant;
            });
        } catch (Throwable $exception) {
            $this->photoStorage->delete($photoPath);

            throw $exception;
        }
    }
}
