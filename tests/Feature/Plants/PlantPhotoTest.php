<?php

declare(strict_types=1);

namespace Tests\Feature\Plants;

use App\Actions\Plants\UpdatePlant;
use App\Models\Plant;
use App\Models\PlantEntry;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PlantPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_plant_can_be_created_with_photo(): void
    {
        $this->post(route('plants.store'), [
            'name' => 'Fikus',
            'plant_type' => 'manual',
            'photo' => UploadedFile::fake()->image('fikus.jpg'),
        ])->assertSessionHasNoErrors();

        $plant = Plant::query()->sole();

        $this->assertNotNull($plant->photo_path);
        Storage::disk('public')->assertExists($plant->photo_path);
    }

    public function test_photo_can_be_added_during_update(): void
    {
        $plant = Plant::factory()->create([
            'photo_path' => null,
        ]);

        $this->put(route('plants.update', $plant), [
            'name' => $plant->name,
            'plant_type' => $plant->plant_type->value,
            'photo' => UploadedFile::fake()->image('new.jpg'),
        ])->assertSessionHasNoErrors();

        $plant->refresh();

        $this->assertNotNull($plant->photo_path);
        Storage::disk('public')->assertExists($plant->photo_path);
    }

    public function test_new_photo_replaces_old_photo(): void
    {
        Storage::disk('public')->put('plants/old.jpg', 'old');

        $plant = Plant::factory()->create([
            'photo_path' => 'plants/old.jpg',
        ]);

        $this->put(route('plants.update', $plant), [
            'name' => $plant->name,
            'plant_type' => $plant->plant_type->value,
            'photo' => UploadedFile::fake()->image('new.jpg'),
        ])->assertSessionHasNoErrors();

        $plant->refresh();

        Storage::disk('public')->assertMissing('plants/old.jpg');
        Storage::disk('public')->assertExists($plant->photo_path);
    }

    public function test_photo_can_be_removed(): void
    {
        Storage::disk('public')->put('plants/photo.jpg', 'photo');

        $plant = Plant::factory()->create([
            'photo_path' => 'plants/photo.jpg',
        ]);

        $this->put(route('plants.update', $plant), [
            'name' => $plant->name,
            'plant_type' => $plant->plant_type->value,
            'remove_photo' => true,
        ])->assertSessionHasNoErrors();

        $plant->refresh();

        $this->assertNull($plant->photo_path);
        Storage::disk('public')->assertMissing('plants/photo.jpg');
    }

    public function test_deleting_plant_deletes_all_photos(): void
    {
        Storage::disk('public')->put('plants/plant.jpg', 'plant');
        Storage::disk('public')->put('plant-entries/entry.jpg', 'entry');

        $plant = Plant::factory()->create([
            'photo_path' => 'plants/plant.jpg',
        ]);

        PlantEntry::factory()->for($plant)->create([
            'current_photo_path' => 'plant-entries/entry.jpg',
        ]);

        $this->delete(route('plants.destroy', $plant))
            ->assertSessionHas('success');

        Storage::disk('public')->assertMissing('plants/plant.jpg');
        Storage::disk('public')->assertMissing('plant-entries/entry.jpg');
    }

    public function test_invalid_photo_preserves_existing_photo(): void
    {
        Storage::disk('public')->put(
            'plants/existing.jpg',
            'existing-photo',
        );

        $plant = Plant::factory()->create([
            'photo_path' => 'plants/existing.jpg',
        ]);

        $this->from(route('plants.edit', $plant))
            ->put(route('plants.update', $plant), [
                'name' => $plant->name,
                'plant_type' => $plant->plant_type->value,
                'photo' => UploadedFile::fake()->create(
                    'document.txt',
                    10,
                    'text/plain',
                ),
            ])
            ->assertRedirect(route('plants.edit', $plant))
            ->assertSessionHasErrors('photo');

        $plant->refresh();

        $this->assertSame(
            'plants/existing.jpg',
            $plant->photo_path,
        );

        Storage::disk('public')->assertExists(
            'plants/existing.jpg',
        );
    }

    public function test_photo_larger_than_two_megabytes_is_rejected(): void
    {
        $plant = Plant::factory()->create();

        $this->from(route('plants.edit', $plant))
            ->put(route('plants.update', $plant), [
                'name' => $plant->name,
                'plant_type' => $plant->plant_type->value,
                'photo' => UploadedFile::fake()
                    ->image('large.jpg')
                    ->size(2049),
            ])
            ->assertSessionHasErrors('photo');

        $plant->refresh();

        $this->assertNull($plant->photo_path);
    }

    public function test_new_photo_is_removed_when_database_update_fails(): void
    {
        Storage::disk('public')->put('plants/old.jpg', 'old-photo');

        $plant = Plant::factory()->create([
            'name' => 'Fikus',
            'photo_path' => 'plants/old.jpg',
        ]);

        try {
            $this->app->make(UpdatePlant::class)->execute(
                $plant,
                ['name' => null],
                UploadedFile::fake()->image('new.jpg'),
            );

            $this->fail('Expected database exception was not thrown.');
        } catch (QueryException) {
            // Oczekiwany błąd kolumny name, która nie może być null.
        }

        $plant->refresh();

        $this->assertSame('plants/old.jpg', $plant->photo_path);
        Storage::disk('public')->assertExists('plants/old.jpg');

        $this->assertSame(
            ['plants/old.jpg'],
            Storage::disk('public')->allFiles('plants'),
        );
    }
}
