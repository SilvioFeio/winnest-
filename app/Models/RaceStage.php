<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaceStage extends Model
{
    protected $fillable = ['race_id', 'stage_type', 'name', 'date', 'participants'];

    protected $casts = [
        'date' => 'date',
    ];

    public function race() {
        return $this->belongsTo(Race::class);
    }

    public function results() {
        return $this->hasMany(Result::class);
    }
}
