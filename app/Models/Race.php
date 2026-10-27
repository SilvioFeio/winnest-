<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// A one-loft race for one season; olr_season_id links it to oneloftrace.live for olr:sync
class Race extends Model
{
    protected $fillable = ['name', 'organiser', 'country', 'season', 'olr_season_id'];

    // The trainings, hotspots and final of this race
    public function stages() {
        return $this->hasMany(RaceStage::class);
    }
}
