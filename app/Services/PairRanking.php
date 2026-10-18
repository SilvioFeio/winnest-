<?php

namespace App\Services;

use App\Models\Pair;

class PairRanking {
    // How important each part is (must add up to 1)
    public $weights = [
        'performance' => 0.6,
        'fertility' => 0.2,
        'survival' => 0.2,
    ];

    // How much each stage type counts
    Public $stageWeights = [
        'training' => 1,
        'hotspot' => 2,
        'final' => 3
    ];

    // Pairs with fewer youngsters than this get a warning
    public $minYoungsters = 2;

    public function rank() {
        $pairs = Pair::with(['cock','hen','fertilityRecord', 'youngsters.results.stage'])->get();

        $ranking = [];

        foreach ($pairs as $pair) {
            $performance = $this->performance($pair);
            $fertility = $this->fertility($pair);
            $survival = $this->survival($pair);

            $total = $this->weights['performance'] * $performance
                    + $this->weights['fertility'] * $fertility
                    + $this->weights['survival'] * $survival;
            

            $ranking[] = [
                'pair_id'     => $pair->id,
                'cock'        => $pair->cock->ring_number,
                'hen'         => $pair->hen->ring_number,
                'season'      => $pair->season,
                'youngsters'  => $pair->youngsters->count(),
                'enough_data' => $pair->youngsters->count() >= $this->minYoungsters,
                'performance' => round($performance, 2),
                'fertility'   => round($fertility, 2),
                'survival'    => round($survival, 2),
                'total'       => round($total, 2),
            ];
        }
        // Highest score first
        usort($ranking, fn ($a, $b) => $b['total'] <=> $a['total']);

        return $ranking;
    }
     // Average result score of all youngsters, finals count more
    private function performance($pair)
    {
        $scoreTotal  = 0;
        $weightTotal = 0;

        foreach ($pair->youngsters as $youngster) {
            foreach ($youngster->results as $result) {
                $participants = $result->stage->participants;
                $score  = ($participants - $result->position + 1) / $participants;
                $weight = $this->stageWeights[$result->stage->stage_type];

                $scoreTotal  += $score * $weight;
                $weightTotal += $weight;
            }
        }

        if ($weightTotal == 0) {
            return 0;
        }

        return $scoreTotal / $weightTotal;
    }

    // Weaned youngsters divided by eggs laid
    private function fertility($pair)
    {
        $record = $pair->fertilityRecord;

        if (! $record || $record->eggs_laid == 0) {
            return 0;
        }

        return $record->weaned / $record->eggs_laid;
    }

    // Youngsters still alive divided by all youngsters
    private function survival($pair)
    {
        $all = $pair->youngsters->count();

        if ($all == 0) {
            return 0;
        }

        $alive = $pair->youngsters->where('status', 'alive')->count();

        return $alive / $all;
    }
}
?>