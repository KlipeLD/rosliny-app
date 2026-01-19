<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    protected $fillable = ['name', 'description', 'photo_path'];

    public function entries()
    {
        return $this->hasMany(PlantEntry::class);
    }


}
