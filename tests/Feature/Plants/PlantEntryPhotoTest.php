<?php

declare(strict_types=1);

namespace Tests\Feature\Plants;

use App\Models\PlantEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PlantEntryPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_photo_can_be_added_to_entry(): void
    {
        $entry = PlantEntry::factory()->create([
            'current_photo_path' => null,
        ]);

        $response = $this->patch(route('entries.update', $entry), [
            'current_photo' => UploadedFile::fake()->image('plant.jpg'),
        ]);

        $response
            ->assertRedirect(route('plants.show', $entry->plant_id))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $entry->refresh();

        $this->assertNotNull($entry->current_photo_path);
        Storage::disk('public')->assertExists(
            $entry->current_photo_path,
        );
    }

    public function test_new_photo_replaces_old_photo(): void
    {
        Storage::disk('public')->put(
            'plant-entries/old.jpg',
            'old-photo',
        );

        $entry = PlantEntry::factory()->create([
            'current_photo_path' => 'plant-entries/old.jpg',
        ]);

        $response = $this->patch(route('entries.update', $entry), [
            'current_photo' => UploadedFile::fake()->image('new.jpg'),
        ]);

        $response->assertSessionHasNoErrors();

        $entry->refresh();

        $this->assertNotNull($entry->current_photo_path);
        $this->assertNotSame(
            'plant-entries/old.jpg',
            $entry->current_photo_path,
        );

        Storage::disk('public')->assertMissing(
            'plant-entries/old.jpg',
        );
        Storage::disk('public')->assertExists(
            $entry->current_photo_path,
        );
    }

    public function test_photo_can_be_removed_from_entry(): void
    {
        Storage::disk('public')->put(
            'plant-entries/photo.jpg',
            'photo',
        );

        $entry = PlantEntry::factory()->create([
            'current_photo_path' => 'plant-entries/photo.jpg',
        ]);

        $response = $this->patch(route('entries.update', $entry), [
            'remove_current_photo' => true,
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $entry->refresh();

        $this->assertNull($entry->current_photo_path);
        Storage::disk('public')->assertMissing(
            'plant-entries/photo.jpg',
        );
    }

    public function test_invalid_photo_preserves_existing_photo(): void
    {
        Storage::disk('public')->put(
            'plant-entries/existing.jpg',
            'existing-photo',
        );

        $entry = PlantEntry::factory()->create([
            'current_photo_path' => 'plant-entries/existing.jpg',
        ]);

        $response = $this
            ->from(route('entries.edit', $entry))
            ->patch(route('entries.update', $entry), [
                'current_photo' => UploadedFile::fake()->create(
                    'document.txt',
                    10,
                    'text/plain',
                ),
            ]);

        $response
            ->assertRedirect(route('entries.edit', $entry))
            ->assertSessionHasErrors('current_photo');

        $entry->refresh();

        $this->assertSame(
            'plant-entries/existing.jpg',
            $entry->current_photo_path,
        );
        Storage::disk('public')->assertExists(
            'plant-entries/existing.jpg',
        );
    }

    public function test_photo_larger_than_four_megabytes_is_rejected(): void
    {
        $entry = PlantEntry::factory()->create([
            'current_photo_path' => null,
        ]);

        $response = $this
            ->from(route('entries.edit', $entry))
            ->patch(route('entries.update', $entry), [
                'current_photo' => UploadedFile::fake()
                    ->image('large.jpg')
                    ->size(4097),
            ]);

        $response
            ->assertRedirect(route('entries.edit', $entry))
            ->assertSessionHasErrors('current_photo');

        $entry->refresh();

        $this->assertNull($entry->current_photo_path);
        Storage::disk('public')->assertDirectoryEmpty(
            'plant-entries',
        );
    }

    public function test_deleting_entry_deletes_its_photo(): void
    {
        Storage::disk('public')->put(
            'plant-entries/photo.jpg',
            'photo',
        );

        $entry = PlantEntry::factory()->create([
            'current_photo_path' => 'plant-entries/photo.jpg',
        ]);

        $response = $this->delete(
            route('entries.destroy', $entry),
        );

        $response
            ->assertRedirect(route('plants.show', $entry->plant_id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('plant_entries', [
            'id' => $entry->id,
        ]);

        Storage::disk('public')->assertMissing(
            'plant-entries/photo.jpg',
        );
    }
}
