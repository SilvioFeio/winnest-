<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FertilityRecord;
use App\Models\Pair;
use App\Models\Pigeon;
use App\Models\Race;
Use App\Models\RaceStage;
use App\Models\Result;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create two races
        $thailand = Race::create(['name' => 'Thailand One Loft', 'country' => 'Thailand', 'season' => 2026]);
        $portugal = Race::create(['name' => 'Portugal Golden Race', 'country' => 'Portugal', 'season' => 2026]);

        // Each race gets a Hot Spot and a Final
        $thaiHotspot = RaceStage::create(['race_id' => $thailand->id, 'stage_type' => 'hotspot', 'name' => 'Hot Spot 1', 'participants' => 600]);
        $thaiFinal   = RaceStage::create(['race_id' => $thailand->id, 'stage_type' => 'final',   'name' => 'Final',      'participants' => 400]);
        $portHotspot = RaceStage::create(['race_id' => $portugal->id, 'stage_type' => 'hotspot', 'name' => 'Hot Spot 1', 'participants' => 500]);
        $portFinal   = RaceStage::create(['race_id' => $portugal->id, 'stage_type' => 'final',   'name' => 'Final',      'participants' => 300]);

        // Create pair (strong pair)
        $cock1 = Pigeon::create(['ring_number' => 'NL-2023-1001', 'sex' => 'cock', 'colour' => 'Blue']);
        $hen1  = Pigeon::create(['ring_number' => 'NL-2023-2001', 'sex' => 'hen',  'colour' => 'Checker']);
        $pair1 = Pair::create(['cock_id' => $cock1->id, 'hen_id' => $hen1->id, 'season' => 2026]);
        FertilityRecord::create(['pair_id' => $pair1->id, 'eggs_laid' => 6, 'fertile_eggs' => 5, 'hatched' => 4, 'weaned' => 4]);

        // Pair 2 (weaker pair)
        $cock2 = Pigeon::create(['ring_number' => 'NL-2023-1002', 'sex' => 'cock', 'colour' => 'Red']);
        $hen2  = Pigeon::create(['ring_number' => 'NL-2023-2002', 'sex' => 'hen',  'colour' => 'Blue']);
        $pair2 = Pair::create(['cock_id' => $cock2->id, 'hen_id' => $hen2->id, 'season' => 2026]);
        FertilityRecord::create(['pair_id' => $pair2->id, 'eggs_laid' => 6, 'fertile_eggs' => 3, 'hatched' => 2, 'weaned' => 2]);

        // 6. Youngsters of pair 1
        $young1 = Pigeon::create(['ring_number' => 'NL-2026-0011', 'sex' => 'cock', 'colour' => 'Blue', 'parent_pair_id' => $pair1->id]);
        $young2 = Pigeon::create(['ring_number' => 'NL-2026-0012', 'sex' => 'hen',  'colour' => 'Blue', 'parent_pair_id' => $pair1->id]);

        // 7. Youngsters of pair 2 (one is lost)
        $young3 = Pigeon::create(['ring_number' => 'NL-2026-0021', 'sex' => 'cock', 'colour' => 'Red', 'parent_pair_id' => $pair2->id]);
        $young4 = Pigeon::create(['ring_number' => 'NL-2026-0022', 'sex' => 'hen',  'colour' => 'Red', 'parent_pair_id' => $pair2->id,
                                  'status' => 'lost', 'status_date' => '2026-06-15']);

        // 8. Race results
        Result::create(['pigeon_id' => $young1->id, 'race_stage_id' => $thaiHotspot->id, 'position' => 12]);
        Result::create(['pigeon_id' => $young1->id, 'race_stage_id' => $thaiFinal->id,   'position' => 5]);
        Result::create(['pigeon_id' => $young2->id, 'race_stage_id' => $portHotspot->id, 'position' => 40]);
        Result::create(['pigeon_id' => $young2->id, 'race_stage_id' => $portFinal->id,   'position' => 18]);
        Result::create(['pigeon_id' => $young3->id, 'race_stage_id' => $thaiHotspot->id, 'position' => 250]);
        Result::create(['pigeon_id' => $young3->id, 'race_stage_id' => $thaiFinal->id,   'position' => 190]);
    }
}