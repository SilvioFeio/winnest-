<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Breeding numbers of one pair; fertility score = weaned / eggs_laid
class FertilityRecord extends Model
{
    protected $fillable = ['pair_id', 'eggs_laid', 'fertile_eggs', 'hatched', 'weaned'];

    // The pair these numbers belong to
    public function pair() {
        return $this->belongsTo(Pair::class);
    }
}
