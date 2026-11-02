<?php

declare(strict_types=1);

namespace Tests\Feature\Plants;

use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PlantValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_plant_requires_a_name_and_valid_type(): void
    {
        $response = $this->from(route('plants.create'))
            ->post(route('plants.store'), [
                'name' => '',
                'plant_type' => 'unknown',
                'watering_interval_days' => 0,
            ]);

        $response
            ->assertRedirect(route('plants.create'))
            ->assertSessionHasErrors(['name', 'plant_type', 'watering_interval_days']);
        $this->assertDatabaseCount('plants', 0);
    }

    public function test_complete_moisture_range_is_required_when_any_value_is_provided(): void
    {
        $response = $this->post(route('plants.store'), [
            'name' => 'Monstera',
            'plant_type' => 'sensor',
            'soil_moisture_min' => 10,
        ]);

        $response->assertSessionHasErrors([
            'soil_moisture_max',
            'soil_moisture_ideal_min',
            'soil_moisture_ideal_max',
        ]);
        $this->assertDatabaseCount('plants', 0);
    }

    public function test_ideal_ranges_must_be_inside_acceptable_ranges(): void
    {
        $response = $this->post(route('plants.store'), [
            'name' => 'Monstera',
            'plant_type' => 'sensor',
            'soil_moisture_min' => 10,
            'soil_moisture_max' => 80,
            'soil_moisture_ideal_min' => 5,
            'soil_moisture_ideal_max' => 90,
            'temp_min' => 10,
            'temp_max' => 30,
            'temp_ideal_min' => 5,
            'temp_ideal_max' => 25,
        ]);

        $response->assertSessionHasErrors([
            'soil_moisture_ideal_min',
            'temp_ideal_min',
        ]);
        $this->assertDatabaseCount('plants', 0);
    }

    public function test_parameter_range_pairs_must_be_complete(): void
    {
        $response = $this->post(route('plants.store'), [
            'name' => 'Monstera',
            'plant_type' => 'manual',
            'ph_min' => 5.5,
            'ec_ideal_max' => 1200,
        ]);

        $response->assertSessionHasErrors([
            'ph_max',
            'ec_ideal_min',
        ]);
        $this->assertDatabaseCount('plants', 0);
    }

    public function test_boundary_values_are_accepted(): void
    {
        $response = $this->post(route('plants.store'), [
            'name' => 'Roślina graniczna',
            'plant_type' => 'sensor',
            'watering_interval_days' => 365,
            'soil_moisture_min' => 0,
            'soil_moisture_max' => 100,
            'soil_moisture_ideal_min' => 0,
            'soil_moisture_ideal_max' => 100,
            'temp_min' => -50,
            'temp_max' => 80,
            'temp_ideal_min' => -50,
            'temp_ideal_max' => 80,
            'ph_min' => 0,
            'ph_max' => 14,
            'ph_ideal_min' => 0,
            'ph_ideal_max' => 14,
        ]);

        $plant = Plant::query()->sole();

        $response->assertRedirect(route('plants.show', $plant));
        $this->assertSame(365, $plant->watering_interval_days);
        $this->assertSame(-50.0, $plant->temp_min);
        $this->assertSame(14.0, $plant->ph_max);
    }

    public function test_update_uses_the_same_validation_rules(): void
    {
        $plant = Plant::factory()->create();

        $response = $this->from(route('plants.edit', $plant))
            ->put(route('plants.update', $plant), [
                'name' => '',
                'plant_type' => 'invalid',
            ]);

        $response
            ->assertRedirect(route('plants.edit', $plant))
            ->assertSessionHasErrors(['name', 'plant_type']);
        $this->assertSame($plant->name, $plant->fresh()->name);
    }
}
