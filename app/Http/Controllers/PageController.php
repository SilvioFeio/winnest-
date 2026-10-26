<?php

namespace App\Http\Controllers;

use App\Models\Pair;
use App\Models\Pigeon;
use App\Models\Race;
use App\Models\Result;
use App\Services\PairRanking;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /** Race Results page: results of one race, straight from the database. */
    public function results(Request $request)
    {
        $races = Race::orderBy('name')->get();
        $race = $races->firstWhere('id', (int) $request->query('race')) ?? $races->first();

        $results = collect();
        if ($race) {
            $results = Result::with(['pigeon.parentPair.cock', 'pigeon.parentPair.hen', 'stage'])
                ->whereHas('stage', fn ($q) => $q->where('race_id', $race->id))
                ->get()
                ->sortBy(fn ($r) => [$r->stage->stage_type === 'final' ? 0 : 1, $r->stage->name, $r->position])
                ->values();
        }

        return view('results', compact('races', 'race', 'results'));
    }

    /** Breeding Pairs page: all youngsters plus the profile of one selected pair. */
    public function pairs(Request $request)
    {
        $ranking = (new PairRanking)->rank();

        $selectedId = (int) $request->query('pair') ?: ($ranking[0]['pair_id'] ?? null);

        $selected = $selectedId
            ? Pair::with(['cock', 'hen', 'fertilityRecord', 'youngsters.results.stage.race'])->find($selectedId)
            : null;

        $rankPosition = null;
        $rankRow = null;
        foreach ($ranking as $index => $row) {
            if ($row['pair_id'] == $selectedId) {
                $rankPosition = $index + 1;
                $rankRow = $row;
                break;
            }
        }

        $youngsters = Pigeon::with(['parentPair.cock', 'parentPair.hen'])
            ->withCount('results')
            ->whereNotNull('parent_pair_id')
            ->orderBy('ring_number')
            ->get();

        return view('pairs', compact('ranking', 'selected', 'selectedId', 'rankPosition', 'rankRow', 'youngsters'));
    }
}
