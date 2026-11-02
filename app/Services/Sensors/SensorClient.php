<?php

declare(strict_types=1);

namespace App\Services\Sensors;

use App\ValueObjects\SensorReading;

interface SensorClient
{
    /**
     * @throws SensorClientException
     */
    public function fetchSoilReading(): SensorReading;
}
