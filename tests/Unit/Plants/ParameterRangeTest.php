<?php

declare(strict_types=1);

namespace Tests\Unit\Plants;

use App\ValueObjects\ParameterRange;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ParameterRangeTest extends TestCase
{
    #[DataProvider('positionCases')]
    public function test_value_position_against_ideal_range(
        float|int $value,
        bool $isBelowIdeal,
        bool $isAboveIdeal,
    ): void {
        $range = new ParameterRange(20, 60, 10, 80);

        $this->assertSame($isBelowIdeal, $range->isBelowIdeal($value));
        $this->assertSame($isAboveIdeal, $range->isAboveIdeal($value));
    }

    public function test_rejects_reversed_ideal_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ParameterRange(60, 20, 10, 80);
    }

    public function test_rejects_reversed_acceptable_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ParameterRange(20, 60, 80, 10);
    }

    public static function positionCases(): array
    {
        return [
            'below ideal' => [19.99, true, false],
            'ideal minimum' => [20, false, false],
            'inside ideal' => [40, false, false],
            'ideal maximum' => [60, false, false],
            'above ideal' => [60.01, false, true],
        ];
    }
}
