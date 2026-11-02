<?php

declare(strict_types=1);

namespace App\Enums;

enum ParameterStatus: string
{
    case Missing = 'missing';
    case Good = 'good';
    case Warning = 'warning';
    case Critical = 'critical';

    public function cssClass(): string
    {
        return match ($this) {
            self::Missing => 'secondary',
            self::Good => 'success',
            self::Warning => 'warning',
            self::Critical => 'danger',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Missing => 'brak',
            self::Good => 'dobry',
            self::Warning => 'uwaga',
            self::Critical => 'krytyczny',
        };
    }
}
