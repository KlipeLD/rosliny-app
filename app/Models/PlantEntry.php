<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantEntry extends Model
{
    protected $fillable = [
        'plant_id','source','payload','note','recorded_at',
        'ts_ms','temp_c','moist_pct','ec_uscm','ph',
        'n_mgkg','p_mgkg','k_mgkg','salt_mgl',
    ];


    protected $casts = [
        'payload' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

}
