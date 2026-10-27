<?php

namespace Tests\Feature;

use App\Models\Pair;
use App\Models\Pigeon;
use App\Models\Race;
use App\Models\RaceStage;
use App\Models\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    // One race with a final and two hotspots, one pair with one youngster that has a result in each
    protected function setUp(): void
    {
        parent::setUp();

        $race = Race::create(['name' => 'Mira One Loft Race 2026', 'country' => 'Portugal', 'season' => 2026]);
        $pair = Pair::create([
            'cock_id' => Pigeon::create(['ring_number' => '24-8077901', 'sex' => 'cock'])->id,
            'hen_id' => Pigeon::create(['ring_number' => '24-8078075', 'sex' => 'hen'])->id,
            'season' => 2026,
        ]);
        $young = Pigeon::create(['ring_number' => '26-8349995', 'sex' => 'cock', 'parent_pair_id' => $pair->id]);

        foreach ([['hotspot', 'Hot Spot 10', 40], ['hotspot', 'Hot Spot 2', 30], ['final', 'Final (310 km)', 97]] as [$type, $name, $position]) {
            $stage = RaceStage::create(['race_id' => $race->id, 'stage_type' => $type, 'name' => $name, 'participants' => 850]);
            Result::create(['pigeon_id' => $young->id, 'race_stage_id' => $stage->id, 'position' => $position]);
        }
    }

    public function test_every_page_loads_with_data(): void
    {
        $this->get('/')->assertOk()->assertSee('Mira One Loft Race 2026')->assertSee('26-8349995 — #97 of 850');
        $this->get('/rankings')->assertOk()->assertSee('Season 2026 leaderboard');
        $this->get('/pairs')->assertOk()->assertSee('2026'); // hatch year of 26-8349995
        $this->get('/pairs?pair=99999')->assertOk();
        $this->get('/results?race=abc')->assertOk();
    }

    public function test_results_show_final_first_then_stages_in_natural_order(): void
    {
        $this->get('/results')->assertOk()->assertSeeInOrder(['Final (310 km)', 'Hot Spot 2', 'Hot Spot 10']);
    }
}
