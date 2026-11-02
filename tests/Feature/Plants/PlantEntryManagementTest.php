<?php

declare(strict_types=1);

namespace Tests\Feature\Plants;

use App\Models\Plant;
use App\Models\PlantEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PlantEntryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_care_entries_can_be_added(): void
    {
        $plant = Plant::factory()->create();

        $response = $this->post(
            route('plants.entries.watering', $plant),
            [
                'actions' => ['watering', 'fertilizing', 'watering'],
                'recorded_at' => '2026-11-01 09:30:00',
                'note' => 'Podlanie i nawożenie',
            ],
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseCount('plant_entries', 2);
        $this->assertDatabaseHas('plant_entries', [
            'plant_id' => $plant->id,
            'source' => 'watering',
            'note' => 'Podlanie i nawożenie',
        ]);
        $this->assertDatabaseHas('plant_entries', [
            'plant_id' => $plant->id,
            'source' => 'fertilizing',
            'note' => 'Podlanie i nawożenie',
        ]);
    }

    public function test_entry_can_be_updated(): void
    {
        $entry = PlantEntry::factory()->soil()->create();

        $response = $this->patch(route('entries.update', $entry), [
            'note' => 'Nowa notatka',
            'temp_c' => 23.5,
            'moist_pct' => 45.2,
            'ec_uscm' => 750,
            'ph' => 6.4,
            'n_mgkg' => 40,
            'p_mgkg' => 20,
            'k_mgkg' => 60,
            'salt_mgl' => 100,
        ]);

        $response
            ->assertRedirect(route('plants.show', $entry->plant_id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('plant_entries', [
            'id' => $entry->id,
            'note' => 'Nowa notatka',
            'temp_c' => 23.5,
            'moist_pct' => 45.2,
            'ec_uscm' => 750,
            'ph' => 6.4,
        ]);
    }

    public function test_entry_can_be_deleted(): void
    {
        $entry = PlantEntry::factory()->create();

        $this->delete(route('entries.destroy', $entry))
            ->assertRedirect(route('plants.show', $entry->plant_id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('plant_entries', [
            'id' => $entry->id,
        ]);
    }
}
