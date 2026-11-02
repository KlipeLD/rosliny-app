<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\PhotoStorage;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

final class PhotoStorageTest extends TestCase
{
    private PhotoStorage $photoStorage;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->photoStorage = $this->app->make(PhotoStorage::class);
    }

    public function test_stores_photo_in_selected_directory(): void
    {
        $path = $this->photoStorage->store(
            UploadedFile::fake()->image('plant.jpg'),
            'plants',
        );

        $this->assertStringStartsWith('plants/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_deletes_multiple_photos_and_ignores_missing_paths(): void
    {
        Storage::disk('public')->put('plants/plant.jpg', 'plant-photo');
        Storage::disk('public')->put('plant-entries/entry.jpg', 'entry-photo');

        $this->photoStorage->deleteMany([
            'plants/plant.jpg',
            null,
            '',
            'plant-entries/entry.jpg',
        ]);

        Storage::disk('public')->assertMissing('plants/plant.jpg');
        Storage::disk('public')->assertMissing('plant-entries/entry.jpg');
    }

    public function test_throws_exception_when_photo_cannot_be_stored(): void
    {
        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('putFile')
            ->once()
            ->andReturnFalse();

        $filesystem = Mockery::mock(Factory::class);
        $filesystem->shouldReceive('disk')
            ->once()
            ->with('public')
            ->andReturn($disk);

        $photoStorage = new PhotoStorage($filesystem);

        $this->expectException(RuntimeException::class);

        $photoStorage->store(
            UploadedFile::fake()->image('plant.jpg'),
            'plants',
        );
    }
}
