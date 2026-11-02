<?php

declare(strict_types=1);

namespace Tests\Feature\Plants;

use App\Enums\PlantEntrySource;
use App\Models\Plant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class FetchSensorReadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.plant_api.base_url', 'http://sensor.test');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_sensor_reading_is_saved(): void
    {
        Carbon::setTestNow('2026-11-02 12:00:00');

        Http::fake([
            '*/soil' => Http::response($this->validPayload()),
        ]);

        $plant = Plant::factory()->sensor()->create();

        $this->post(route('plants.entries.fetch', $plant))
            ->assertSessionHas('success');

        $entry = $plant->entries()->sole();

        $this->assertSame(PlantEntrySource::Soil, $entry->source);
        $this->assertSame(12345, $entry->ts_ms);
        $this->assertSame(22.5, (float) $entry->temp_c);
        $this->assertSame(45.2, (float) $entry->moist_pct);
        $this->assertSame(750, $entry->ec_uscm);
        $this->assertSame(6.4, (float) $entry->ph);
        $this->assertSame(40, $entry->n_mgkg);
        $this->assertSame(20, $entry->p_mgkg);
        $this->assertSame(60, $entry->k_mgkg);
        $this->assertSame(100, $entry->salt_mgl);
        $expectedPayload = $this->validPayload();

        unset($expectedPayload['ok']);

        $this->assertSame($expectedPayload, $entry->payload);
        $this->assertSame(
            '2026-11-02 12:00:00',
            $entry->recorded_at->format('Y-m-d H:i:s'),
        );

        Http::assertSent(
            fn ($request): bool => $request->method() === 'GET'
                && str_ends_with($request->url(), '/soil'),
        );

        Http::assertSentCount(1);
    }

    public function test_invalid_json_does_not_create_entry(): void
    {
        Http::fake([
            '*/soil' => Http::response(
                'invalid-json',
                200,
                ['Content-Type' => 'application/json'],
            ),
        ]);

        $plant = Plant::factory()->sensor()->create();

        $this->post(route('plants.entries.fetch', $plant))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('plant_entries', 0);
    }

    public function test_manual_plant_cannot_fetch_sensor_data(): void
    {
        Http::fake();

        $plant = Plant::factory()->create([
            'plant_type' => 'manual',
        ]);

        $this->post(route('plants.entries.fetch', $plant))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('plant_entries', 0);
        Http::assertNothingSent();
    }

    public function test_http_error_does_not_create_entry(): void
    {
        Http::fake([
            '*/soil' => Http::response([], 500),
        ]);

        $plant = Plant::factory()->sensor()->create();

        $this->post(route('plants.entries.fetch', $plant))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('plant_entries', 0);
    }

    public function test_unsuccessful_payload_does_not_create_entry(): void
    {
        Http::fake([
            '*/soil' => Http::response(['ok' => false]),
        ]);

        $plant = Plant::factory()->sensor()->create();

        $this->post(route('plants.entries.fetch', $plant))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('plant_entries', 0);
    }

    private function validPayload(): array
    {
        return [
            'ok' => true,
            'ts_ms' => 12345,
            'temp_c' => 22.5,
            'moist_pct' => 45.2,
            'ec_uscm' => 750,
            'ph' => 6.4,
            'n_mgkg' => 40,
            'p_mgkg' => 20,
            'k_mgkg' => 60,
            'salt_mgl' => 100,
        ];
    }

    public function test_incomplete_reading_does_not_create_entry(): void
    {
        $payload = $this->validPayload();

        unset($payload['moist_pct']);

        Http::fake([
            '*/soil' => Http::response($payload),
        ]);

        $plant = Plant::factory()->sensor()->create();

        $this->post(route('plants.entries.fetch', $plant))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('plant_entries', 0);
    }

    public function test_reading_with_invalid_field_type_does_not_create_entry(): void
    {
        $payload = $this->validPayload();
        $payload['temp_c'] = '22.5';

        Http::fake([
            '*/soil' => Http::response($payload),
        ]);

        $plant = Plant::factory()->sensor()->create();

        $this->post(route('plants.entries.fetch', $plant))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('plant_entries', 0);
    }
}
