<?php

namespace Tests\Feature;

use App\Models\Pigeon;
use App\Models\Race;
use App\Models\RaceStage;
use App\Models\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncOneLoftRaceTest extends TestCase
{
    use RefreshDatabase;

    private function arrival(int $order, string $ring): array
    {
        return ['arrival_order' => $order, 'pigeon' => ['ring_description' => $ring]];
    }

    private function fakeOneLoftRace(int $finalStatus = 200): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*/season/3335/flightsAndTrainings' => Http::response([
                'running_flights' => [
                    ['id' => 100, 'flight_type' => 'race', 'name' => 'FINAL AMARANTE', 'trigger_basketings_counter' => 1817],
                ],
                'stopped_flights' => [
                    ['id' => 101, 'flight_type' => 'race', 'name' => 'Semi-Final', 'trigger_basketings_counter' => 1019],
                    ['id' => 102, 'flight_type' => 'race', 'name' => 'Pigeon auction', 'trigger_basketings_counter' => 0],
                    ['flight_type' => 'race', 'name' => 'Broken row without id'], // malformed data must not crash the sync
                ],
            ]),
            '*/flights/100/results/live*' => Http::response(['all_flight_results' => [
                $this->arrival(17, 'NL26-8349740'),
                $this->arrival(727, 'NL26-8350003'), // not in our database
            ]], $finalStatus),
            '*/flights/101/results/live*' => Http::response(['all_flight_results' => [
                $this->arrival(5, '8349740/26 (R1)'),
                ['pigeon' => ['ring_description' => 'NL26-8349740']], // no arrival_order
                ['arrival_order' => 9],                              // no pigeon
            ]]),
        ]);
    }

    private function seedRaceWithCsvFinal(): RaceStage
    {
        $race = Race::create(['name' => 'Algarve Pigeons Golden Race 2026', 'country' => 'Portugal', 'season' => 2026]);
        Pigeon::create(['ring_number' => '26-8349740', 'sex' => 'cock']);

        return RaceStage::create(['race_id' => $race->id, 'stage_type' => 'final', 'name' => 'Final (471 km)', 'participants' => 1817]);
    }

    public function test_sync_links_csv_stage_stores_our_pigeons_and_is_safe_to_rerun(): void
    {
        $this->fakeOneLoftRace();
        $csvFinal = $this->seedRaceWithCsvFinal();

        $this->artisan('olr:sync', ['--season' => 3335, '--race' => 'Algarve Pigeons Golden Race 2026'])
            ->expectsOutputToContain('pigeon 26-8350003 arrived #727 but is not in our database')
            ->expectsOutputToContain("Skipped flight 'Pigeon auction'")
            ->expectsOutputToContain('Skipped a flight without an id')
            ->assertSuccessful();

        // The CSV final is reused, the semi-final becomes a new hotspot, the auction is skipped
        $this->assertSame(100, $csvFinal->fresh()->olr_flight_id);
        $this->assertSame('hotspot', RaceStage::where('olr_flight_id', 101)->value('stage_type'));
        $this->assertSame(2, RaceStage::count());

        // Only our pigeon gets results, with the overall arrival order as position
        $this->assertSame(17, Result::where('race_stage_id', $csvFinal->id)->value('position'));
        $this->assertSame(2, Result::count());

        $this->artisan('olr:sync', ['--all' => true])->assertSuccessful();
        $this->assertSame(2, RaceStage::count());
        $this->assertSame(2, Result::count());
    }

    public function test_failed_fetch_writes_no_results(): void
    {
        $this->fakeOneLoftRace(finalStatus: 500);
        $csvFinal = $this->seedRaceWithCsvFinal();

        $this->artisan('olr:sync', ['--season' => 3335, '--race' => 'Algarve Pigeons Golden Race 2026'])
            ->expectsOutputToContain("Could not load results for 'Final (471 km)'")
            ->assertSuccessful();

        $this->assertSame(0, Result::where('race_stage_id', $csvFinal->id)->count());
        $this->assertNull($csvFinal->fresh()->olr_synced_at);
    }

    public function test_dry_run_saves_nothing(): void
    {
        $this->fakeOneLoftRace();
        $this->seedRaceWithCsvFinal();

        $this->artisan('olr:sync', ['--season' => 3335, '--race' => 'Algarve Pigeons Golden Race 2026', '--dry-run' => true])
            ->expectsOutputToContain('Dry run: nothing was saved.')
            ->assertSuccessful();

        $this->assertSame(0, Result::count());
        $this->assertNull(Race::first()->olr_season_id);
    }
}
