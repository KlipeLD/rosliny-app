<?php

namespace Tests\Unit;

use App\Models\Plant;
use Tests\TestCase;

class PlantParameterRangesTest extends TestCase
{
    public function test_custom_ideal_ranges_override_default_status_ranges(): void
    {
        $plant = new Plant([
            'temp_min' => 16,
            'temp_max' => 28,
            'temp_ideal_min' => 20,
            'temp_ideal_max' => 24,
            'ph_min' => 5.6,
            'ph_max' => 6.8,
            'ph_ideal_min' => 5.8,
            'ph_ideal_max' => 6.4,
            'ec_min' => 200,
            'ec_max' => 1200,
            'ec_ideal_min' => 300,
            'ec_ideal_max' => 900,
            'n_min' => 20,
            'n_max' => 90,
            'n_ideal_min' => 30,
            'n_ideal_max' => 70,
            'p_min' => 10,
            'p_max' => 50,
            'p_ideal_min' => 15,
            'p_ideal_max' => 35,
            'k_min' => 30,
            'k_max' => 120,
            'k_ideal_min' => 40,
            'k_ideal_max' => 90,
            'salt_min' => 0,
            'salt_max' => 180,
            'salt_ideal_min' => 10,
            'salt_ideal_max' => 120,
        ]);

        $this->assertSame([20.0, 24.0, 16.0, 28.0], $plant->temperatureRanges());
        $this->assertSame([5.8, 6.4, 5.6, 6.8], $plant->phRanges());
        $this->assertSame([300.0, 900.0, 200.0, 1200.0], $plant->ecRanges());
        $this->assertSame([30.0, 70.0, 20.0, 90.0], $plant->nitrogenRanges());
        $this->assertSame([15.0, 35.0, 10.0, 50.0], $plant->phosphorusRanges());
        $this->assertSame([40.0, 90.0, 30.0, 120.0], $plant->potassiumRanges());
        $this->assertSame([10.0, 120.0, 0.0, 180.0], $plant->saltRanges());
    }

    public function test_default_ranges_are_used_when_custom_range_is_incomplete(): void
    {
        $plant = new Plant([
            'temp_ideal_min' => 20,
        ]);

        $this->assertSame([18, 26, 15, 30], $plant->temperatureRanges());
    }

    public function test_custom_acceptable_range_can_override_defaults_without_custom_ideal_range(): void
    {
        $plant = new Plant([
            'temp_min' => 12,
            'temp_max' => 32,
        ]);

        $this->assertSame([18, 26, 12.0, 32.0], $plant->temperatureRanges());
    }
}
