<?php

namespace Tests\Unit;

use App\Models\Plant;
use Tests\TestCase;

class PlantParameterRangesTest extends TestCase
{
    public function test_custom_ideal_ranges_override_default_status_ranges(): void
    {
        $plant = new Plant([
            'temp_ideal_min' => 20,
            'temp_ideal_max' => 24,
            'ph_ideal_min' => 5.8,
            'ph_ideal_max' => 6.4,
            'ec_ideal_min' => 300,
            'ec_ideal_max' => 900,
            'n_ideal_min' => 30,
            'n_ideal_max' => 70,
            'p_ideal_min' => 15,
            'p_ideal_max' => 35,
            'k_ideal_min' => 40,
            'k_ideal_max' => 90,
            'salt_ideal_min' => 10,
            'salt_ideal_max' => 120,
        ]);

        $this->assertSame([20.0, 24.0, 15, 30], $plant->temperatureRanges());
        $this->assertSame([5.8, 6.4, 5.5, 7.8], $plant->phRanges());
        $this->assertSame([300.0, 900.0, 100, 2000], $plant->ecRanges());
        $this->assertSame([30.0, 70.0, 10, 100], $plant->nitrogenRanges());
        $this->assertSame([15.0, 35.0, 5, 80], $plant->phosphorusRanges());
        $this->assertSame([40.0, 90.0, 10, 150], $plant->potassiumRanges());
        $this->assertSame([10.0, 120.0, 200, 400], $plant->saltRanges());
    }

    public function test_default_ranges_are_used_when_custom_range_is_incomplete(): void
    {
        $plant = new Plant([
            'temp_ideal_min' => 20,
        ]);

        $this->assertSame([18, 26, 15, 30], $plant->temperatureRanges());
    }
}
