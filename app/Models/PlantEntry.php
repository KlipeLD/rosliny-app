<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlantEntrySource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id', 'source', 'payload', 'note', 'recorded_at',
        'current_photo_path',
        'ts_ms', 'temp_c', 'moist_pct', 'ec_uscm', 'ph',
        'n_mgkg', 'p_mgkg', 'k_mgkg', 'salt_mgl',
    ];

    protected function casts(): array
    {
        return [
            'source' => PlantEntrySource::class,
            'payload' => 'array',
            'recorded_at' => 'immutable_datetime',
            'temp_c' => 'float',
            'moist_pct' => 'float',
            'ph' => 'float',
            'ec_uscm' => 'integer',
            'n_mgkg' => 'integer',
            'p_mgkg' => 'integer',
            'k_mgkg' => 'integer',
            'salt_mgl' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Plant, $this>
     */
    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }
}
