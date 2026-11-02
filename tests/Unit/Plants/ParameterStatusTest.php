<?php

declare(strict_types=1);

namespace Tests\Unit\Plants;

use App\Enums\ParameterStatus;
use App\ValueObjects\ParameterRange;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ParameterStatusTest extends TestCase
{
    #[DataProvider('statusCases')]
    public function test_parameter_status(
        float|int|null $value,
        ParameterStatus $expected,
    ): void {
        $this->assertSame(
            $expected,
            (new ParameterRange(20, 60, 10, 80))->status($value),
        );
    }

    #[DataProvider('presentationCases')]
    public function test_status_has_presentation_data(
        ParameterStatus $status,
        string $expectedCssClass,
        string $expectedLabel,
    ): void {
        $this->assertSame($expectedCssClass, $status->cssClass());
        $this->assertSame($expectedLabel, $status->label());
    }

    public static function statusCases(): array
    {
        return [
            'missing value' => [
                null,
                ParameterStatus::Missing,
            ],
            'ideal minimum' => [
                20,
                ParameterStatus::Good,
            ],
            'inside ideal range' => [
                40,
                ParameterStatus::Good,
            ],
            'ideal maximum' => [
                60,
                ParameterStatus::Good,
            ],
            'acceptable minimum' => [
                10,
                ParameterStatus::Warning,
            ],
            'below ideal range' => [
                19.99,
                ParameterStatus::Warning,
            ],
            'above ideal range' => [
                60.01,
                ParameterStatus::Warning,
            ],
            'acceptable maximum' => [
                80,
                ParameterStatus::Warning,
            ],
            'below acceptable range' => [
                9.99,
                ParameterStatus::Critical,
            ],
            'above acceptable range' => [
                80.01,
                ParameterStatus::Critical,
            ],
        ];
    }

    public static function presentationCases(): array
    {
        return [
            'missing' => [ParameterStatus::Missing, 'secondary', 'brak'],
            'good' => [ParameterStatus::Good, 'success', 'dobry'],
            'warning' => [ParameterStatus::Warning, 'warning', 'uwaga'],
            'critical' => [ParameterStatus::Critical, 'danger', 'krytyczny'],
        ];
    }
}
