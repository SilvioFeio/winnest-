<?php

namespace App\Console\Commands;

use App\Models\Pigeon;
use App\Models\Race;
use App\Models\RaceStage;
use App\Models\Result;
use App\Services\OneLoftRace;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

// php artisan olr:sync — copies our pigeons' arrivals from oneloftrace.live into results
class SyncOneLoftRace extends Command
{
    protected $signature = 'olr:sync
                            {--season= : OneLoftRace season id to link to a race (from the URL ?season=...)}
                            {--race= : Name of our race that the season belongs to}
                            {--search=Koopman : Team name to look for in the results}
                            {--all : Also fetch finished flights again}
                            {--dry-run : Show what would change without saving anything}';

    protected $description = 'Fetch race results for our pigeons from oneloftrace.live';

    /** Problems found while syncing; printed at the end. */
    private array $warnings = [];

    /** What happened, per kind. */
    private array $counts = ['flights fetched' => 0, 'stages linked' => 0, 'stages created' => 0, 'results created' => 0, 'results updated' => 0];

    // Optionally links a season to a race, then syncs every flight of every linked race in one transaction
    public function handle(OneLoftRace $olr): int
    {
        DB::beginTransaction();

        if ($this->option('season')) {
            $race = Race::where('name', $this->option('race'))->first();
            if (!$race) {
                $this->error("Race '{$this->option('race')}' not found. Use --race=\"exact race name\".");
                DB::rollBack();
                return self::FAILURE;
            }
            $race->update(['olr_season_id' => (int) $this->option('season')]);
        }

        $races = Race::whereNotNull('olr_season_id')->get();
        if ($races->isEmpty()) {
            $this->error('No race is linked yet. Run: php artisan olr:sync --season=3335 --race="Algarve Pigeons Golden Race 2026"');
            DB::rollBack();
            return self::FAILURE;
        }

        foreach ($races as $race) {
            try {
                $flights = $olr->flights($race->olr_season_id);
            } catch (Throwable $e) {
                $this->warnings[] = "Could not load flights for {$race->name}: {$e->getMessage()}";
                continue;
            }

            foreach ($flights['running_flights'] ?? [] as $flight) {
                $this->syncFlight($olr, $race, $flight, true);
            }
            foreach ($flights['stopped_flights'] ?? [] as $flight) {
                $this->syncFlight($olr, $race, $flight, false);
            }
        }

        $this->option('dry-run') ? DB::rollBack() : DB::commit();

        $this->table(['What', 'Count'], collect($this->counts)->map(fn ($n, $what) => [$what, $n])->values()->all());
        foreach ($this->warnings as $warning) {
            $this->warn($warning);
        }
        if ($this->option('dry-run')) {
            $this->info('Dry run: nothing was saved.');
        }
        $this->info(count($this->warnings) . ' warning(s). Sync finished.');

        return self::SUCCESS;
    }

    // Finds or links the stage, fetches its arrivals and saves those of pigeons we know
    private function syncFlight(OneLoftRace $olr, Race $race, array $flight, bool $running): void
    {
        // The data comes from an outside website, so a flight without an id is skipped instead of crashing the sync
        if (!is_int($flight['id'] ?? null)) {
            $this->warnings[] = 'Skipped a flight without an id in the OneLoftRace data.';
            return;
        }

        $stage = RaceStage::where('olr_flight_id', $flight['id'])->first() ?? $this->linkStage($race, $flight);
        if (!$stage) {
            return;
        }

        // Finished flights only need fetching once
        if (!$running && $stage->olr_synced_at && !$this->option('all')) {
            return;
        }

        try {
            $arrivals = $olr->results($flight['id'], $this->option('search'));
        } catch (Throwable $e) {
            $this->warnings[] = "Could not load results for '{$stage->name}': {$e->getMessage()}";
            return;
        }
        $this->counts['flights fetched']++;

        foreach ($arrivals as $arrival) {
            $ring = Pigeon::normaliseRing((string) ($arrival['pigeon']['ring_description'] ?? ''));
            $order = (int) ($arrival['arrival_order'] ?? 0);
            if ($ring === '' || $order < 1) {
                continue; // incomplete row from the website: nothing we can store
            }

            $pigeon = Pigeon::where('ring_number', $ring)->first();
            if (!$pigeon) {
                $this->warnings[] = "'{$stage->name}': pigeon $ring arrived #$order but is not in our database.";
                continue;
            }

            $result = Result::updateOrCreate(
                ['pigeon_id' => $pigeon->id, 'race_stage_id' => $stage->id],
                ['position' => $order]
            );
            if ($result->wasRecentlyCreated) {
                $this->counts['results created']++;
            } elseif ($result->wasChanged()) {
                $this->counts['results updated']++;
            }
        }

        $stage->update([
            'participants' => (int) ($flight['trigger_basketings_counter'] ?? $stage->participants),
            'olr_synced_at' => now(),
        ]);
    }

    /** Link a flight to the stage that was imported from CSV, or create a new stage for it. */
    private function linkStage(Race $race, array $flight): ?RaceStage
    {
        $type = $this->stageType($flight);
        if (!$type) {
            $this->warnings[] = "Skipped flight '" . ($flight['name'] ?? '?') . "' ({$flight['id']}): cannot tell if it is a training, hotspot or final.";
            return null;
        }

        $participants = (int) ($flight['trigger_basketings_counter'] ?? 0);

        $stage = RaceStage::where('race_id', $race->id)
            ->where('stage_type', $type)
            ->where('participants', $participants)
            ->whereNull('olr_flight_id')
            ->first();

        if ($stage) {
            $stage->update(['olr_flight_id' => $flight['id']]);
            $this->counts['stages linked']++;
            return $stage;
        }

        $this->counts['stages created']++;

        return RaceStage::create([
            'race_id' => $race->id,
            'stage_type' => $type,
            'name' => trim((string) ($flight['name'] ?? '')) ?: "Flight {$flight['id']}",
            'participants' => max($participants, 1),
            'olr_flight_id' => $flight['id'],
        ]);
    }

    /** OneLoftRace only knows "training" and "race", so the stage type comes from the flight name. */
    private function stageType(array $flight): ?string
    {
        $name = strtolower($flight['name'] ?? '');

        return match (true) {
            ($flight['flight_type'] ?? '') === 'training', str_contains($name, 'treino'), str_contains($name, 'training') => 'training',
            str_contains($name, 'semi') => 'hotspot', // a semi-final counts as a hotspot
            (bool) preg_match('/hot\s?spot/', $name) => 'hotspot',
            str_contains($name, 'final') => 'final',
            default => null,
        };
    }
}
