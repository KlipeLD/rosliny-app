<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class SensorReading
{
    public function __construct(
        public int $tsMs,
        public float $tempC,
        public float $moisturePercent,
        public int $ecUsCm,
        public float $ph,
        public int $nitrogenMgKg,
        public int $phosphorusMgKg,
        public int $potassiumMgKg,
        public int $saltMgL,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tsMs: self::requiredInteger($data, 'ts_ms'),
            tempC: self::requiredFloat($data, 'temp_c'),
            moisturePercent: self::requiredFloat($data, 'moist_pct'),
            ecUsCm: self::requiredInteger($data, 'ec_uscm'),
            ph: self::requiredFloat($data, 'ph'),
            nitrogenMgKg: self::requiredInteger($data, 'n_mgkg'),
            phosphorusMgKg: self::requiredInteger($data, 'p_mgkg'),
            potassiumMgKg: self::requiredInteger($data, 'k_mgkg'),
            saltMgL: self::requiredInteger($data, 'salt_mgl'),
        );
    }

    /**
     * @return array{
     *     ts_ms: int,
     *     temp_c: float,
     *     moist_pct: float,
     *     ec_uscm: int,
     *     ph: float,
     *     n_mgkg: int,
     *     p_mgkg: int,
     *     k_mgkg: int,
     *     salt_mgl: int
     * }
     */
    public function toArray(): array
    {
        return [
            'ts_ms' => $this->tsMs,
            'temp_c' => $this->tempC,
            'moist_pct' => $this->moisturePercent,
            'ec_uscm' => $this->ecUsCm,
            'ph' => $this->ph,
            'n_mgkg' => $this->nitrogenMgKg,
            'p_mgkg' => $this->phosphorusMgKg,
            'k_mgkg' => $this->potassiumMgKg,
            'salt_mgl' => $this->saltMgL,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function requiredInteger(array $data, string $key): int
    {
        $value = $data[$key] ?? null;

        if (! is_int($value)) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must be an integer.', $key),
            );
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function requiredFloat(array $data, string $key): float
    {
        $value = $data[$key] ?? null;

        if (! is_int($value) && ! is_float($value)) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must be numeric.', $key),
            );
        }

        return (float) $value;
    }
}
