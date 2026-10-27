<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// A breeding pair (cock + hen) for one season; its youngsters' results decide its ranking
class Pair extends Model
{
    protected $fillable = ['cock_id', 'hen_id', 'season'];

    // The father, via pairs.cock_id
    public function cock() {
        return $this->belongsTo(Pigeon::class, 'cock_id');
    }

    // The mother, via pairs.hen_id
    public function hen() {
        return $this->belongsTo(Pigeon::class, 'hen_id');
    }

    // Pigeons whose parent_pair_id points to this pair
    public function youngsters() {
        return $this->hasMany(Pigeon::class, 'parent_pair_id');
    }

    // Eggs laid / hatched / weaned this season, used for the fertility score
    public function fertilityRecord() {
        return $this->hasOne(FertilityRecord::class);
    }

}
