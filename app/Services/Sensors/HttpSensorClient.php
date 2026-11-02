<?php

declare(strict_types=1);

namespace App\Services\Sensors;

use App\ValueObjects\SensorReading;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use InvalidArgumentException;

final readonly class HttpSensorClient implements SensorClient
{
    public function __construct(
        private HttpFactory $http,
        private ConfigRepository $config,
    ) {}

    public function fetchSoilReading(): SensorReading
    {
        $baseUrl = $this->config->get('services.plant_api.base_url');

        if (! is_string($baseUrl) || trim($baseUrl) === '') {
            throw SensorClientException::missingConfiguration();
        }

        try {
            $response = $this->http
                ->timeout(5)
                ->acceptJson()
                ->get(rtrim($baseUrl, '/').'/soil');
        } catch (ConnectionException $exception) {
            throw SensorClientException::connectionFailed($exception);
        }

        if (! $response->successful()) {
            throw SensorClientException::httpError($response->status());
        }

        $data = $response->json();

        if (! is_array($data) || ($data['ok'] ?? null) !== true) {
            throw SensorClientException::invalidResponse();
        }

        try {
            return SensorReading::fromArray($data);
        } catch (InvalidArgumentException) {
            throw SensorClientException::invalidResponse();
        }
    }
}
