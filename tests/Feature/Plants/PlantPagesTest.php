<?php

declare(strict_types=1);

namespace Tests\Feature\Plants;

use App\Models\Plant;
use App\Models\PlantEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PlantPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_plant_list(): void
    {
        $this->get(route('home'))
            ->assertRedirect(route('plants.index'));
    }

    public function test_plant_list_is_displayed(): void
    {
        $plant = Plant::factory()->create([
            'name' => 'Monstera testowa',
        ]);

        $this->get(route('plants.index'))
            ->assertOk()
            ->assertViewIs('plants.index')
            ->assertSee('Monstera testowa');
    }

    public function test_plant_create_page_is_displayed(): void
    {
        $this->get(route('plants.create'))
            ->assertOk()
            ->assertViewIs('plants.create');
    }

    public function test_plant_details_are_displayed(): void
    {
        $plant = Plant::factory()->create();

        $this->get(route('plants.show', $plant))
            ->assertOk()
            ->assertViewIs('plants.show')
            ->assertViewHas(
                'plant',
                fn (Plant $viewPlant): bool => $viewPlant->is($plant),
            )
            ->assertSee($plant->name);
    }

    public function test_plant_edit_page_is_displayed(): void
    {
        $plant = Plant::factory()->create();

        $this->get(route('plants.edit', $plant))
            ->assertOk()
            ->assertViewIs('plants.edit');
    }

    public function test_entries_page_is_displayed(): void
    {
        $plant = Plant::factory()->create();

        $this->get(route('plants.entries.index', $plant))
            ->assertOk()
            ->assertViewIs('entries.index')
            ->assertViewHas(
                'plant',
                fn (Plant $viewPlant): bool => $viewPlant->is($plant),
            );
    }

    public function test_entry_edit_page_is_displayed(): void
    {
        $entry = PlantEntry::factory()->create();

        $this->get(route('entries.edit', $entry))
            ->assertOk()
            ->assertViewIs('entries.edit')
            ->assertViewHas(
                'entry',
                fn (PlantEntry $viewEntry): bool => $viewEntry->is($entry),
            );
    }

    public function test_missing_plant_returns_not_found(): void
    {
        $this->get(route('plants.show', 999999))
            ->assertNotFound();
    }

    public function test_missing_entry_returns_not_found(): void
    {
        $this->get(route('entries.edit', 999999))
            ->assertNotFound();
    }
}
