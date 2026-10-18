<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pigeon extends Model
{
    protected $fillable = ['ring_number', 'sex', 'colour', 'parent_pair_id', 'status', 'status_date'];

    protected $casts = [
        'status_date' => 'date',
    ];

    public function parentPair() {
        return $this->belongsTo(Pair::class, 'parent_pair_id');
    }

    public function results() {
        return $this->hasMany(Result::class);
    }
}
