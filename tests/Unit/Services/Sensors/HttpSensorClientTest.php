<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Sensors;

use App\Services\Sensors\HttpSensorClient;
use App\Services\Sensors\SensorClient;
use App\Services\Sensors\SensorClientException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class HttpSensorClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.plant_api.base_url', 'http://sensor.test');
    }

    public function test_interface_resolves_to_http_client(): void
    {
        $this->assertInstanceOf(
            HttpSensorClient::class,
            $this->app->make(SensorClient::class),
        );
    }

    public function test_returns_valid_soil_reading(): void
    {
        Http::fake([
            'http://sensor.test/soil' => Http::response(
                $this->validPayload(),
            ),
        ]);

        $reading = $this->client()->fetchSoilReading();

        $this->assertSame(12345, $reading->tsMs);
        $this->assertSame(22.5, $reading->tempC);
        $this->assertSame(45.2, $reading->moisturePercent);
        $this->assertSame(750, $reading->ecUsCm);
        $this->assertSame(6.4, $reading->ph);
        $this->assertSame(40, $reading->nitrogenMgKg);
        $this->assertSame(20, $reading->phosphorusMgKg);
        $this->assertSame(60, $reading->potassiumMgKg);
        $this->assertSame(100, $reading->saltMgL);

        Http::assertSent(
            fn ($request): bool => $request->url()
                === 'http://sensor.test/soil',
        );
    }

    public function test_throws_exception_for_http_error(): void
    {
        Http::fake([
            'http://sensor.test/soil' => Http::response([], 503),
        ]);

        $this->expectException(SensorClientException::class);
        $this->expectExceptionMessage('HTTP 503');

        $this->client()->fetchSoilReading();
    }

    public function test_throws_exception_for_invalid_response(): void
    {
        Http::fake([
            'http://sensor.test/soil' => Http::response(['ok' => false]),
        ]);

        $this->expectException(SensorClientException::class);
        $this->expectExceptionMessage('niepoprawne dane');

        $this->client()->fetchSoilReading();
    }

    public function test_throws_exception_when_connection_fails(): void
    {
        Http::fake([
            'http://sensor.test/soil' => Http::failedConnection('offline'),
        ]);

        $this->expectException(SensorClientException::class);
        $this->expectExceptionMessage('Nie udało się połączyć');

        $this->client()->fetchSoilReading();
    }

    public function test_throws_exception_when_base_url_is_not_configured(): void
    {
        config()->set('services.plant_api.base_url');

        Http::fake();

        try {
            $this->client()->fetchSoilReading();
            $this->fail('Expected SensorClientException was not thrown.');
        } catch (SensorClientException $exception) {
            $this->assertStringContainsString('Brak konfiguracji', $exception->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_throws_exception_when_required_field_is_missing(): void
    {
        $payload = $this->validPayload();

        unset($payload['moist_pct']);

        Http::fake([
            'http://sensor.test/soil' => Http::response($payload),
        ]);

        $this->expectException(SensorClientException::class);
        $this->expectExceptionMessage('niepoprawne dane');

        $this->client()->fetchSoilReading();
    }

    public function test_throws_exception_when_field_has_invalid_type(): void
    {
        $payload = $this->validPayload();
        $payload['moist_pct'] = '45.2';

        Http::fake([
            'http://sensor.test/soil' => Http::response($payload),
        ]);

        $this->expectException(SensorClientException::class);
        $this->expectExceptionMessage('niepoprawne dane');

        $this->client()->fetchSoilReading();
    }

    private function client(): SensorClient
    {
        return $this->app->make(SensorClient::class);
    }

    /**
     * @return array<string, mixed>
     */
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
}
