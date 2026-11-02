<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Http\UploadedFile;
use RuntimeException;

final class PhotoStorage
{
    private const DISK = 'public';

    public function __construct(
        private readonly FilesystemFactory $filesystem,
    ) {}

    public function store(UploadedFile $photo, string $directory): string
    {
        $path = $this->filesystem
            ->disk(self::DISK)
            ->putFile($directory, $photo);

        if ($path === false) {
            throw new RuntimeException('The photo could not be stored.');
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $this->filesystem->disk(self::DISK)->delete($path);
    }

    /**
     * @param  iterable<string|null>  $paths
     */
    public function deleteMany(iterable $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }
}
