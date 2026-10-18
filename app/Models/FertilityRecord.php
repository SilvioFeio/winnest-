<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FertilityRecord extends Model
{
    protected $fillable = ['pair_id', 'eggs_laid', 'fertile_eggs', 'hatched', 'weaned'];

    public function pair() {
        return $this->belongsTo(Pair::class);
    }
}
