<?php

namespace Tests\Feature;

use App\Models\FertilityRecord;
use App\Models\Pair;
use App\Models\Pigeon;
use App\Models\Race;
use App\Models\RaceStage;
use App\Models\Result;
use App\Services\PairRanking;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PairRankingTest extends TestCase
{
    use RefreshDatabase;

    // Builds a pair with its parents; returns the pair
    private function pair(string $cock, string $hen): Pair
    {
        return Pair::create([
            'cock_id' => Pigeon::create(['ring_number' => $cock, 'sex' => 'cock'])->id,
            'hen_id' => Pigeon::create(['ring_number' => $hen, 'sex' => 'hen'])->id,
            'season' => 2026,
        ]);
    }

    public function test_scores_match_the_hand_calculated_formula(): void
    {
        $race = Race::create(['name' => 'Test Race', 'country' => 'PT', 'season' => 2026]);
        $final = RaceStage::create(['race_id' => $race->id, 'stage_type' => 'final', 'name' => 'Final', 'participants' => 100]);
        $hotspot = RaceStage::create(['race_id' => $race->id, 'stage_type' => 'hotspot', 'name' => 'Hot Spot 1', 'participants' => 50]);

        $strong = $this->pair('26-0000001', '26-0000002');
        FertilityRecord::create(['pair_id' => $strong->id, 'eggs_laid' => 10, 'weaned' => 5]);
        $young = Pigeon::create(['ring_number' => '26-0000011', 'sex' => 'cock', 'parent_pair_id' => $strong->id]);
        Pigeon::create(['ring_number' => '26-0000012', 'sex' => 'hen', 'parent_pair_id' => $strong->id, 'status' => 'lost']);
        Result::create(['pigeon_id' => $young->id, 'race_stage_id' => $final->id, 'position' => 1]);    // score 1.00, weight 3
        Result::create(['pigeon_id' => $young->id, 'race_stage_id' => $hotspot->id, 'position' => 50]); // score 0.02, weight 2

        $this->pair('26-0000003', '26-0000004'); // no youngsters, no fertility record

        $ranking = (new PairRanking)->rank();

        // performance (1*3 + 0.02*2) / 5 = 0.608, fertility 5/10, survival 1/2
        $this->assertSame([0.61, 0.5, 0.5, 0.56, true], [
            $ranking[0]['performance'], $ranking[0]['fertility'], $ranking[0]['survival'], $ranking[0]['total'], $ranking[0]['enough_data'],
        ]);
        $this->assertSame([0.0, 0.0, 0.0, 0.0, false], [
            $ranking[1]['performance'], $ranking[1]['fertility'], $ranking[1]['survival'], $ranking[1]['total'], $ranking[1]['enough_data'],
        ]);
    }

    public function test_stage_with_zero_participants_does_not_crash(): void
    {
        $race = Race::create(['name' => 'Test Race', 'country' => 'PT', 'season' => 2026]);
        $stage = RaceStage::create(['race_id' => $race->id, 'stage_type' => 'final', 'name' => 'Final', 'participants' => 0]);
        $pair = $this->pair('26-0000001', '26-0000002');
        $young = Pigeon::create(['ring_number' => '26-0000011', 'sex' => 'cock', 'parent_pair_id' => $pair->id]);
        Result::create(['pigeon_id' => $young->id, 'race_stage_id' => $stage->id, 'position' => 1]);

        $this->assertSame(0.0, (new PairRanking)->rank()[0]['performance']);
    }

    public function test_a_parent_in_a_pair_cannot_be_deleted(): void
    {
        $pair = $this->pair('26-0000001', '26-0000002');

        $this->expectException(QueryException::class);
        $pair->cock->delete();
    }
}
