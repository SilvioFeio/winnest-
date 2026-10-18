<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = ['pigeon_id', 'race_stage_id', 'position'];

    public function pigeon() {
        return $this->belongsTo(Pigeon::class);
    }

    public function stage() {
        return $this->belongsTo(RaceStage::class, 'race_stage_id');
    }
}
