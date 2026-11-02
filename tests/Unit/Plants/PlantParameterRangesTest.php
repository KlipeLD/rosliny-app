<?php

declare(strict_types=1);

namespace Tests\Unit\Plants;

use App\Models\Plant;
use App\ValueObjects\ParameterRange;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class PlantParameterRangesTest extends TestCase
{
    #[DataProvider('parameterRanges')]
    public function test_default_ranges_are_returned(
        string $method,
        string $prefix,
        ParameterRange $defaults,
    ): void {
        $plant = new Plant;

        $this->assertEquals(
            $defaults,
            $plant->{$method}(),
        );
    }

    #[DataProvider('parameterRanges')]
    public function test_custom_ideal_and_acceptable_ranges_are_returned(
        string $method,
        string $prefix,
        ParameterRange $defaults,
    ): void {
        $plant = new Plant([
            $prefix.'_min' => 11,
            $prefix.'_max' => 32,
            $prefix.'_ideal_min' => 21,
            $prefix.'_ideal_max' => 27,
        ]);

        $this->assertEquals(
            new ParameterRange(21.0, 27.0, 11.0, 32.0),
            $plant->{$method}(),
        );
    }

    #[DataProvider('parameterRanges')]
    public function test_custom_ideal_range_preserves_default_acceptable_range(
        string $method,
        string $prefix,
        ParameterRange $defaults,
    ): void {
        $plant = new Plant([
            $prefix.'_ideal_min' => 21,
            $prefix.'_ideal_max' => 27,
        ]);

        $this->assertEquals(
            new ParameterRange(
                21.0,
                27.0,
                $defaults->acceptableMin,
                $defaults->acceptableMax,
            ),
            $plant->{$method}(),
        );
    }

    #[DataProvider('parameterRanges')]
    public function test_custom_acceptable_range_preserves_default_ideal_range(
        string $method,
        string $prefix,
        ParameterRange $defaults,
    ): void {
        $plant = new Plant([
            $prefix.'_min' => 11,
            $prefix.'_max' => 32,
        ]);

        $this->assertEquals(
            new ParameterRange(
                $defaults->idealMin,
                $defaults->idealMax,
                11.0,
                32.0,
            ),
            $plant->{$method}(),
        );
    }

    #[DataProvider('parameterRanges')]
    public function test_incomplete_ideal_range_does_not_override_defaults(
        string $method,
        string $prefix,
        ParameterRange $defaults,
    ): void {
        $plant = new Plant([
            $prefix.'_ideal_min' => 21,
        ]);

        $this->assertEquals(
            $defaults,
            $plant->{$method}(),
        );
    }

    #[DataProvider('parameterRanges')]
    public function test_incomplete_acceptable_range_does_not_override_defaults(
        string $method,
        string $prefix,
        ParameterRange $defaults,
    ): void {
        $plant = new Plant([
            $prefix.'_min' => 11,
        ]);

        $this->assertEquals(
            $defaults,
            $plant->{$method}(),
        );
    }

    public function test_default_moisture_ranges_are_returned(): void
    {
        $plant = new Plant;

        $this->assertEquals(
            new ParameterRange(20, 60, 10, 80),
            $plant->moistureRanges(),
        );
    }

    public function test_custom_moisture_ranges_are_returned(): void
    {
        $plant = new Plant([
            'soil_moisture_min' => 15,
            'soil_moisture_max' => 75,
            'soil_moisture_ideal_min' => 25,
            'soil_moisture_ideal_max' => 55,
        ]);

        $this->assertEquals(
            new ParameterRange(25.0, 55.0, 15.0, 75.0),
            $plant->moistureRanges(),
        );
    }

    public function test_partial_moisture_ranges_use_defaults_for_missing_values(): void
    {
        $plant = new Plant([
            'soil_moisture_min' => 15,
            'soil_moisture_ideal_max' => 55,
        ]);

        $this->assertEquals(
            new ParameterRange(20, 55.0, 15.0, 80),
            $plant->moistureRanges(),
        );
    }

    public static function parameterRanges(): array
    {
        return [
            'temperature' => [
                'temperatureRanges',
                'temp',
                new ParameterRange(18, 26, 15, 30),
            ],
            'ph' => [
                'phRanges',
                'ph',
                new ParameterRange(6.0, 7.2, 5.5, 7.8),
            ],
            'electrical conductivity' => [
                'ecRanges',
                'ec',
                new ParameterRange(200, 1200, 100, 2000),
            ],
            'nitrogen' => [
                'nitrogenRanges',
                'n',
                new ParameterRange(20, 60, 10, 100),
            ],
            'phosphorus' => [
                'phosphorusRanges',
                'p',
                new ParameterRange(10, 40, 5, 80),
            ],
            'potassium' => [
                'potassiumRanges',
                'k',
                new ParameterRange(20, 80, 10, 150),
            ],
            'salt' => [
                'saltRanges',
                'salt',
                new ParameterRange(0, 200, 200, 400),
            ],
        ];
    }
}
