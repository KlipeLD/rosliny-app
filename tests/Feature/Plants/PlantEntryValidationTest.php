<?php

declare(strict_types=1);

namespace Tests\Feature\Plants;

use App\Models\Plant;
use App\Models\PlantEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class PlantEntryValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_care_entry_requires_valid_actions_and_date(): void
    {
        $plant = Plant::factory()->create();

        $response = $this->from(route('plants.show', $plant))
            ->post(route('plants.entries.watering', $plant), [
                'actions' => ['watering', 'invalid-action'],
                'recorded_at' => 'not-a-date',
            ]);

        $response
            ->assertRedirect(route('plants.show', $plant))
            ->assertSessionHasErrors(['actions.1', 'recorded_at']);
        $this->assertDatabaseCount('plant_entries', 0);
    }

    public function test_plant_entry_rejects_invalid_measurements_and_photo(): void
    {
        $entry = PlantEntry::factory()->create([
            'note' => 'Nie zmieniaj',
            'ec_uscm' => 500,
        ]);

        $response = $this->from(route('entries.edit', $entry))
            ->patch(route('entries.update', $entry), [
                'note' => str_repeat('a', 2001),
                'temp_c' => 'not-a-number',
                'ec_uscm' => 12.5,
                'current_photo' => UploadedFile::fake()->create('document.txt', 10, 'text/plain'),
            ]);

        $response
            ->assertRedirect(route('entries.edit', $entry))
            ->assertSessionHasErrors(['note', 'temp_c', 'ec_uscm', 'current_photo']);

        $entry->refresh();
        $this->assertSame('Nie zmieniaj', $entry->note);
        $this->assertSame(500, $entry->ec_uscm);
    }

    public function test_measurements_outside_allowed_ranges_are_rejected(): void
    {
        $entry = PlantEntry::factory()->create();

        $response = $this->patch(route('entries.update', $entry), [
            'temp_c' => -51,
            'moist_pct' => 101,
            'ec_uscm' => -1,
            'ph' => 14.1,
            'n_mgkg' => -1,
            'p_mgkg' => -1,
            'k_mgkg' => -1,
            'salt_mgl' => -1,
        ]);

        $response->assertSessionHasErrors([
            'temp_c',
            'moist_pct',
            'ec_uscm',
            'ph',
            'n_mgkg',
            'p_mgkg',
            'k_mgkg',
            'salt_mgl',
        ]);
    }

    public function test_measurement_boundary_values_are_accepted(): void
    {
        $entry = PlantEntry::factory()->create();

        $response = $this->patch(route('entries.update', $entry), [
            'temp_c' => -50,
            'moist_pct' => 100,
            'ec_uscm' => 0,
            'ph' => 14,
            'n_mgkg' => 0,
            'p_mgkg' => 0,
            'k_mgkg' => 0,
            'salt_mgl' => 0,
        ]);

        $response->assertSessionHasNoErrors();

        $entry->refresh();

        $this->assertSame(-50.0, (float) $entry->temp_c);
        $this->assertSame(100.0, (float) $entry->moist_pct);
        $this->assertSame(14.0, (float) $entry->ph);
    }

    public function test_actions_are_required(): void
    {
        $plant = Plant::factory()->create();

        $this->post(route('plants.entries.watering', $plant), [
            'recorded_at' => '2026-11-01 10:00:00',
        ])->assertSessionHasErrors('actions');

        $this->assertDatabaseCount('plant_entries', 0);
    }

    public function test_empty_actions_array_is_rejected(): void
    {
        $plant = Plant::factory()->create();

        $this->post(route('plants.entries.watering', $plant), [
            'actions' => [],
            'recorded_at' => '2026-11-01 10:00:00',
        ])->assertSessionHasErrors('actions');

        $this->assertDatabaseCount('plant_entries', 0);
    }

    public function test_recorded_at_is_required(): void
    {
        $plant = Plant::factory()->create();

        $this->post(route('plants.entries.watering', $plant), [
            'actions' => ['watering'],
        ])->assertSessionHasErrors('recorded_at');

        $this->assertDatabaseCount('plant_entries', 0);
    }
}
