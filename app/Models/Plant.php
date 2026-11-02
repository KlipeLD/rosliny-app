<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlantType;
use App\ValueObjects\ParameterRange;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'plant_type',
        'photo_path',
        'soil_moisture_min',
        'soil_moisture_max',
        'soil_moisture_ideal_min',
        'soil_moisture_ideal_max',
        'watering_interval_days',
        'temp_min',
        'temp_max',
        'temp_ideal_min',
        'temp_ideal_max',
        'ph_min',
        'ph_max',
        'ph_ideal_min',
        'ph_ideal_max',
        'ec_min',
        'ec_max',
        'ec_ideal_min',
        'ec_ideal_max',
        'n_min',
        'n_max',
        'n_ideal_min',
        'n_ideal_max',
        'p_min',
        'p_max',
        'p_ideal_min',
        'p_ideal_max',
        'k_min',
        'k_max',
        'k_ideal_min',
        'k_ideal_max',
        'salt_min',
        'salt_max',
        'salt_ideal_min',
        'salt_ideal_max',
    ];

    protected $casts = [
        'plant_type' => PlantType::class,
        'soil_moisture_min' => 'float',
        'soil_moisture_max' => 'float',
        'soil_moisture_ideal_min' => 'float',
        'soil_moisture_ideal_max' => 'float',
        'watering_interval_days' => 'integer',
        'temp_min' => 'float',
        'temp_max' => 'float',
        'temp_ideal_min' => 'float',
        'temp_ideal_max' => 'float',
        'ph_min' => 'float',
        'ph_max' => 'float',
        'ph_ideal_min' => 'float',
        'ph_ideal_max' => 'float',
        'ec_min' => 'float',
        'ec_max' => 'float',
        'ec_ideal_min' => 'float',
        'ec_ideal_max' => 'float',
        'n_min' => 'float',
        'n_max' => 'float',
        'n_ideal_min' => 'float',
        'n_ideal_max' => 'float',
        'p_min' => 'float',
        'p_max' => 'float',
        'p_ideal_min' => 'float',
        'p_ideal_max' => 'float',
        'k_min' => 'float',
        'k_max' => 'float',
        'k_ideal_min' => 'float',
        'k_ideal_max' => 'float',
        'salt_min' => 'float',
        'salt_max' => 'float',
        'salt_ideal_min' => 'float',
        'salt_ideal_max' => 'float',
    ];

    /**
     * @return HasMany<PlantEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(PlantEntry::class);
    }

    public function moistureRanges(): ParameterRange
    {
        return new ParameterRange(
            $this->soil_moisture_ideal_min ?? 20,
            $this->soil_moisture_ideal_max ?? 60,
            $this->soil_moisture_min ?? 10,
            $this->soil_moisture_max ?? 80,
        );
    }

    public function temperatureRanges(): ParameterRange
    {
        return $this->rangesWithIdeal('temp', new ParameterRange(18, 26, 15, 30));
    }

    public function phRanges(): ParameterRange
    {
        return $this->rangesWithIdeal('ph', new ParameterRange(6.0, 7.2, 5.5, 7.8));
    }

    public function ecRanges(): ParameterRange
    {
        return $this->rangesWithIdeal('ec', new ParameterRange(200, 1200, 100, 2000));
    }

    public function nitrogenRanges(): ParameterRange
    {
        return $this->rangesWithIdeal('n', new ParameterRange(20, 60, 10, 100));
    }

    public function phosphorusRanges(): ParameterRange
    {
        return $this->rangesWithIdeal('p', new ParameterRange(10, 40, 5, 80));
    }

    public function potassiumRanges(): ParameterRange
    {
        return $this->rangesWithIdeal('k', new ParameterRange(20, 80, 10, 150));
    }

    public function saltRanges(): ParameterRange
    {
        return $this->rangesWithIdeal('salt', new ParameterRange(0, 200, 200, 400));
    }

    private function rangesWithIdeal(string $prefix, ParameterRange $defaults): ParameterRange
    {
        $idealMin = $this->{$prefix.'_ideal_min'};
        $idealMax = $this->{$prefix.'_ideal_max'};
        $acceptableMin = $this->{$prefix.'_min'};
        $acceptableMax = $this->{$prefix.'_max'};

        if ($idealMin !== null && $idealMax !== null) {
            $resolvedIdealMin = (float) $idealMin;
            $resolvedIdealMax = (float) $idealMax;
        } else {
            $resolvedIdealMin = $defaults->idealMin;
            $resolvedIdealMax = $defaults->idealMax;
        }

        if ($acceptableMin !== null && $acceptableMax !== null) {
            $resolvedAcceptableMin = (float) $acceptableMin;
            $resolvedAcceptableMax = (float) $acceptableMax;
        } else {
            $resolvedAcceptableMin = $defaults->acceptableMin;
            $resolvedAcceptableMax = $defaults->acceptableMax;
        }

        return new ParameterRange(
            $resolvedIdealMin,
            $resolvedIdealMax,
            $resolvedAcceptableMin,
            $resolvedAcceptableMax,
        );
    }
}
