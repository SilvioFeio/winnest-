<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    protected $fillable = ['name', 'organiser', 'country', 'season'];

    public function stages() {
        return $this->hasMany(RaceStage::class);
    }
}
