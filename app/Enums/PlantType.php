<?php

declare(strict_types=1);

namespace App\Enums;

enum PlantType: string
{
    case Sensor = 'sensor';
    case Manual = 'manual';
}
