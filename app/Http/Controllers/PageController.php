<?php

namespace App\Http\Controllers;

use App\Models\Pair;
use App\Models\Pigeon;
use App\Models\Race;
use App\Models\Result;
use App\Services\PairRanking;
use Illuminate\Http\Request;

// Builds the data for every page; the views only display it
class PageController extends Controller
{
    /** Home page: live totals plus every race with our best youngster in its final. */
    public function home()
    {
        $races = Race::withCount('stages')->orderByDesc('season')->orderBy('name')->get();

        // Best (lowest) final position per race, keyed by race id
        $bestFinals = Result::with(['pigeon', 'stage'])
            ->whereHas('stage', fn ($q) => $q->where('stage_type', 'final'))
            ->orderBy('position')
            ->get()
            ->unique(fn ($r) => $r->stage->race_id)
            ->keyBy(fn ($r) => $r->stage->race_id);

        return view('home', [
            'races' => $races,
            'bestFinals' => $bestFinals,
            'pairCount' => Pair::count(),
            'youngsterCount' => Pigeon::whereNotNull('parent_pair_id')->count(),
        ]);
    }

    /** Pair Rankings page: every pair with its score breakdown, best first. */
    public function rankings()
    {
        $ranking = (new PairRanking)->rank();
        $season = collect($ranking)->max('season');

        return view('rankings', compact('ranking', 'season'));
    }

    /** Race Results page: results of one stage of one race, straight from the database. */
    public function results(Request $request)
    {
        $races = Race::orderBy('name')->get();
        $race = $races->firstWhere('id', (int) $request->query('race')) ?? $races->first();

        // Only stages that have results: finals first, then natural order ("Hot Spot 2" before "Hot Spot 10")
        $stages = $race
            ? $race->stages()->has('results')->get()
                ->sort(fn ($a, $b) => [$b->stage_type === 'final', strnatcasecmp($a->name, $b->name)]
                    <=> [$a->stage_type === 'final', 0])
                ->values()
            : collect();

        // ?stage=ID picks a stage; without it (or for another race) the first stage is shown
        $stage = $stages->firstWhere('id', (int) $request->query('stage')) ?? $stages->first();

        $results = $stage
            ? Result::with(['pigeon.parentPair.cock', 'pigeon.parentPair.hen'])
                ->where('race_stage_id', $stage->id)
                ->orderBy('position')
                ->get()
            : collect();

        // Most recent OneLoftRace sync of any stage in this race (null if never synced)
        $lastSynced = $race?->stages()->max('olr_synced_at');

        return view('results', compact('races', 'race', 'stages', 'stage', 'results', 'lastSynced'));
    }

    /** Breeding Pairs page: all youngsters plus the profile of one selected pair. */
    public function pairs(Request $request)
    {
        $ranking = (new PairRanking)->rank();

        // ?pair=ID picks a pair; without it the best-ranked pair is shown
        $selectedId = (int) $request->query('pair') ?: ($ranking[0]['pair_id'] ?? null);

        $selected = $selectedId
            ? Pair::with(['cock', 'hen', 'fertilityRecord', 'youngsters.results.stage.race'])->find($selectedId)
            : null;

        // Find the selected pair's place and scores in the ranking
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
