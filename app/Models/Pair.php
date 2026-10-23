<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pair extends Model
{
    protected $fillable = ['cock_id', 'hen_id', 'season'];

    public function cock() {
        return $this->belongsTo(Pigeon::class, 'cock_id');
    }

    public function hen() {
        return $this->belongsTo(Pigeon::class, 'hen_id');
    }

    public function youngsters() {
        return $this->hasMany(Pigeon::class, 'parent_pair_id');
    }

    public function fertilityRecord() {
        return $this->hasOne(FertilityRecord::class);
    }

}
