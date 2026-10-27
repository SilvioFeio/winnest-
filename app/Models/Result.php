<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// One pigeon's position in one stage (unique per pigeon + stage)
class Result extends Model
{
    protected $fillable = ['pigeon_id', 'race_stage_id', 'position'];

    // The pigeon that flew
    public function pigeon() {
        return $this->belongsTo(Pigeon::class);
    }

    // The stage it flew in (column is race_stage_id, so the key is given explicitly)
    public function stage() {
        return $this->belongsTo(RaceStage::class, 'race_stage_id');
    }
}
