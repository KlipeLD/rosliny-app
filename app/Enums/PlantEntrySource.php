<?php

declare(strict_types=1);

namespace App\Enums;

enum PlantEntrySource: string
{
    case Soil = 'soil';
    case Watering = 'watering';
    case Fertilizing = 'fertilizing';
    case Repotting = 'repotting';
}
