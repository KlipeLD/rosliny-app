<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Enums\ParameterStatus;
use InvalidArgumentException;

final readonly class ParameterRange
{
    public function __construct(
        public float|int $idealMin,
        public float|int $idealMax,
        public float|int $acceptableMin,
        public float|int $acceptableMax,
    ) {
        if ($idealMin > $idealMax) {
            throw new InvalidArgumentException('The ideal minimum cannot be greater than the ideal maximum.');
        }

        if ($acceptableMin > $acceptableMax) {
            throw new InvalidArgumentException('The acceptable minimum cannot be greater than the acceptable maximum.');
        }
    }

    public function status(float|int|null $value): ParameterStatus
    {
        if ($value === null) {
            return ParameterStatus::Missing;
        }

        if ($value >= $this->idealMin && $value <= $this->idealMax) {
            return ParameterStatus::Good;
        }

        if ($value >= $this->acceptableMin && $value <= $this->acceptableMax) {
            return ParameterStatus::Warning;
        }

        return ParameterStatus::Critical;
    }

    public function isBelowIdeal(float|int $value): bool
    {
        return $value < $this->idealMin;
    }

    public function isAboveIdeal(float|int $value): bool
    {
        return $value > $this->idealMax;
    }
}
