<?php

declare(strict_types=1);

namespace App\Actions\PlantEntries;

use App\Enums\PlantEntrySource;
use App\Models\Plant;
use App\Models\PlantEntry;
use App\ValueObjects\SensorReading;

final class StoreSensorReading
{
    public function execute(
        Plant $plant,
        SensorReading $reading,
    ): PlantEntry {
        return $plant->entries()->create([
            'source' => PlantEntrySource::Soil,
            'payload' => $reading->toArray(),
            'recorded_at' => now(),
            'ts_ms' => $reading->tsMs,
            'temp_c' => $reading->tempC,
            'moist_pct' => $reading->moisturePercent,
            'ec_uscm' => $reading->ecUsCm,
            'ph' => $reading->ph,
            'n_mgkg' => $reading->nitrogenMgKg,
            'p_mgkg' => $reading->phosphorusMgKg,
            'k_mgkg' => $reading->potassiumMgKg,
            'salt_mgl' => $reading->saltMgL,
        ]);
    }
}
