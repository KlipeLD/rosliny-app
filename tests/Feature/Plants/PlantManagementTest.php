<?php

declare(strict_types=1);

namespace Tests\Feature\Plants;

use App\Models\Plant;
use App\Models\PlantEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PlantManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_plant_can_be_created(): void
    {
        $response = $this->post(route('plants.store'), [
            'name' => 'Fikus sprężysty',
            'description' => 'Roślina do salonu',
            'plant_type' => 'manual',
            'watering_interval_days' => 7,
        ]);

        $plant = Plant::query()->sole();

        $response
            ->assertRedirect(route('plants.show', $plant))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('plants', [
            'id' => $plant->id,
            'name' => 'Fikus sprężysty',
            'description' => 'Roślina do salonu',
            'plant_type' => 'manual',
            'watering_interval_days' => 7,
        ]);
    }

    public function test_plant_can_be_updated(): void
    {
        $plant = Plant::factory()->create([
            'name' => 'Stara nazwa',
            'plant_type' => 'manual',
        ]);

        $response = $this->put(route('plants.update', $plant), [
            'name' => 'Nowa nazwa',
            'description' => 'Zmieniony opis',
            'plant_type' => 'sensor',
            'watering_interval_days' => 5,
            'soil_moisture_min' => 10,
            'soil_moisture_max' => 80,
            'soil_moisture_ideal_min' => 20,
            'soil_moisture_ideal_max' => 60,
        ]);

        $response
            ->assertRedirect(route('plants.show', $plant))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('plants', [
            'id' => $plant->id,
            'name' => 'Nowa nazwa',
            'description' => 'Zmieniony opis',
            'plant_type' => 'sensor',
            'watering_interval_days' => 5,
        ]);
    }

    public function test_plant_can_be_deleted_with_its_entries(): void
    {
        $plant = Plant::factory()->create();
        $entry = PlantEntry::factory()->for($plant)->create();

        $response = $this->delete(route('plants.destroy', $plant));

        $response
            ->assertRedirect(route('plants.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('plants', [
            'id' => $plant->id,
        ]);
        $this->assertDatabaseMissing('plant_entries', [
            'id' => $entry->id,
        ]);
    }
}
