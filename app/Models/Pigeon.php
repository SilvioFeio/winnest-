<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Any pigeon: a parent (no parent pair) or a youngster (parent_pair_id set); ring_number is unique
class Pigeon extends Model
{
    protected $fillable = ['ring_number', 'sex', 'colour', 'parent_pair_id', 'status', 'status_date'];

    protected $casts = [
        'status_date' => 'date',
    ];

    // The pair this youngster was bred from (null for parents)
    public function parentPair() {
        return $this->belongsTo(Pair::class, 'parent_pair_id');
    }

    // This pigeon's placings in race stages
    public function results() {
        return $this->hasMany(Result::class);
    }

    /** Hatch year read from the ring: "26-8349977" gives 2026, "NL-2026-0011" gives 2026, otherwise null. */
    public function hatchYear(): ?int
    {
        $ring = self::normaliseRing($this->ring_number);

        if (preg_match('/^(\d{2})-/', $ring, $m)) {
            return 2000 + (int) $m[1];
        }

        return preg_match('/-(\d{4})-/', $ring, $m) ? (int) $m[1] : null;
    }

    /** "NL 24-8077898", "NL24-8077898", "8077898/24" and "8077898/24 (R1)" all become "24-8077898". */
    public static function normaliseRing(string $ring): string
    {
        $ring = preg_replace('/\s*\(.*\)$/', '', trim($ring));
        $ring = preg_replace('/^[A-Za-z]{1,3}\s*(?=\d)/', '', $ring);

        return preg_replace('/^(\d+)\/(\d{2})$/', '$2-$1', $ring);
    }
}
