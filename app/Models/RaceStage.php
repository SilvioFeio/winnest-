<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// One flight of a race (training, hotspot or final); participants is used to score positions
class RaceStage extends Model
{
    protected $fillable = ['race_id', 'stage_type', 'name', 'date', 'participants', 'olr_flight_id', 'olr_synced_at'];

    protected $casts = [
        'date' => 'date',
        'olr_synced_at' => 'datetime',
    ];

    // The race this stage belongs to
    public function race() {
        return $this->belongsTo(Race::class);
    }

    // Our pigeons' placings in this stage
    public function results() {
        return $this->hasMany(Result::class);
    }
}
