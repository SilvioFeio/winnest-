<?php

namespace App\Console\Commands;

use App\Models\Pair;
use App\Models\Pigeon;
use App\Models\Race;
use App\Models\RaceStage;
use App\Models\Result;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPigeons extends Command
{
    protected $signature = 'import:pigeons
                            {--path=database/data : Folder with the CSV files}
                            {--season=2026 : Season the pairs are registered for}';

    protected $description = 'Import pigeons, parents, pairs, races and results from the CSV files';

    /** Problems found while importing; printed at the end. */
    private array $warnings = [];

    /** How many rows were newly created per table. */
    private array $created = ['parents' => 0, 'youngsters' => 0, 'pairs' => 0, 'races' => 0, 'stages' => 0, 'results' => 0];

    public function handle(): int
    {
        $dir = base_path($this->option('path'));
        $season = (int) $this->option('season');

        $files = ['race_entries.csv', 'races.csv', 'results.csv'];
        foreach ($files as $file) {
            if (!is_file("$dir/$file")) {
                $this->error("Missing file: $dir/$file");
                return self::FAILURE;
            }
        }

        $entries = $this->readCsv("$dir/race_entries.csv", ['ring_number', 'sex', 'colour', 'father_ring', 'mother_ring', 'race']);
        $races = $this->readCsv("$dir/races.csv", ['name', 'country', 'season']);
        $results = $this->readCsv("$dir/results.csv", ['ring_number', 'race', 'stage_type', 'stage_name', 'participants', 'position']);

        // parents.csv is optional: it only adds colours of parents
        $parentColours = [];
        if (is_file("$dir/parents.csv")) {
            foreach (($this->readCsv("$dir/parents.csv", ["ring_number", "colour"]) ?? []) as $row) {
                $parentColours[$this->ring($row['ring_number'])] = trim($row['colour']);
            }
        }

        if ($entries === null || $races === null || $results === null) {
            return self::FAILURE;
        }

        DB::transaction(function () use ($entries, $races, $results, $parentColours, $season) {
            $this->importRaces($races);
            $this->importEntries($entries, $parentColours, $season);
            $this->importResults($results);
        });

        $this->table(['Table', 'Newly created'], collect($this->created)->map(fn ($n, $t) => [$t, $n])->values()->all());

        foreach ($this->warnings as $warning) {
            $this->warn($warning);
        }
        $this->info(count($this->warnings) . ' warning(s). Import finished.');

        return self::SUCCESS;
    }

    private function importRaces(array $rows): void
    {
        foreach ($rows as $row) {
            $race = Race::firstOrCreate(
                ['name' => trim($row['name']), 'season' => (int) $row['season']],
                ['organiser' => trim($row['organiser'] ?? '') ?: null, 'country' => trim($row['country'])]
            );
            $this->created['races'] += $race->wasRecentlyCreated ? 1 : 0;
        }
    }

    private function importEntries(array $rows, array $parentColours, int $season): void
    {
        $seen = [];

        foreach ($rows as $row) {
            $ring = $this->ring($row['ring_number']);
            $fatherRing = $this->ring($row['father_ring']);
            $motherRing = $this->ring($row['mother_ring']);

            if ($ring === '' || $fatherRing === '' || $motherRing === '') {
                $this->warnings[] = "Skipped entry '{$row['ring_number']}': ring, father or mother is empty.";
                continue;
            }

            if (isset($seen[$ring]) && $seen[$ring] !== "$fatherRing|$motherRing") {
                $this->warnings[] = "Pigeon $ring appears twice with different parents; the last one is used.";
            }
            $seen[$ring] = "$fatherRing|$motherRing";

            $father = $this->parent($fatherRing, 'cock', $parentColours);
            $mother = $this->parent($motherRing, 'hen', $parentColours);

            $pair = Pair::firstOrCreate([
                'cock_id' => $father->id,
                'hen_id' => $mother->id,
                'season' => $season,
            ]);
            $this->created['pairs'] += $pair->wasRecentlyCreated ? 1 : 0;

            $sex = strtolower(trim($row['sex']));
            if (!in_array($sex, ['cock', 'hen', 'unknown'], true)) {
                $this->warnings[] = "Pigeon $ring has sex '$sex'; stored as unknown.";
                $sex = 'unknown';
            }

            $pigeon = Pigeon::updateOrCreate(
                ['ring_number' => $ring],
                [
                    'sex' => $sex,
                    'colour' => trim($row['colour']) ?: null,
                    'parent_pair_id' => $pair->id,
                    'status' => 'alive',
                ]
            );
            $this->created['youngsters'] += $pigeon->wasRecentlyCreated ? 1 : 0;
        }
    }

    private function importResults(array $rows): void
    {
        foreach ($rows as $row) {
            $ring = $this->ring($row['ring_number']);
            $pigeon = Pigeon::where('ring_number', $ring)->first();
            $race = Race::where('name', trim($row['race']))->first();

            if (!$pigeon) {
                $this->warnings[] = "Result skipped: pigeon $ring does not exist.";
                continue;
            }
            if (!$race) {
                $this->warnings[] = "Result skipped for $ring: race '{$row['race']}' is not in races.csv.";
                continue;
            }
            if (!in_array($row['stage_type'], ['training', 'hotspot', 'final'], true)) {
                $this->warnings[] = "Result skipped for $ring: unknown stage type '{$row['stage_type']}'.";
                continue;
            }
            if ((int) $row['position'] < 1 || (int) $row['position'] > (int) $row['participants']) {
                $this->warnings[] = "Result skipped for $ring: position {$row['position']} is not within {$row['participants']} participants.";
                continue;
            }

            $stage = RaceStage::firstOrCreate(
                ['race_id' => $race->id, 'stage_type' => $row['stage_type'], 'name' => trim($row['stage_name'])],
                ['participants' => (int) $row['participants']]
            );
            $this->created['stages'] += $stage->wasRecentlyCreated ? 1 : 0;

            if ($stage->participants !== (int) $row['participants']) {
                $this->warnings[] = "Stage '{$stage->name}' has different participant numbers in the CSV ({$stage->participants} vs {$row['participants']}); the first is kept.";
            }

            $result = Result::updateOrCreate(
                ['pigeon_id' => $pigeon->id, 'race_stage_id' => $stage->id],
                ['position' => (int) $row['position']]
            );
            $this->created['results'] += $result->wasRecentlyCreated ? 1 : 0;
        }
    }

    /** Find or create a parent pigeon (father = cock, mother = hen). */
    private function parent(string $ring, string $sex, array $colours): Pigeon
    {
        $pigeon = Pigeon::firstOrCreate(
            ['ring_number' => $ring],
            ['sex' => $sex, 'colour' => $colours[$ring] ?? null, 'status' => 'alive']
        );
        $this->created['parents'] += $pigeon->wasRecentlyCreated ? 1 : 0;

        if ($pigeon->sex !== $sex && $pigeon->sex !== 'unknown') {
            $this->warnings[] = "Pigeon $ring is used as {$sex} but is stored as {$pigeon->sex}.";
        }
        if (!$pigeon->colour && isset($colours[$ring])) {
            $pigeon->update(['colour' => $colours[$ring]]);
        }

        return $pigeon;
    }

    /** Remove a country prefix: "NL 24-8077898" and "24-8077898" become the same ring. */
    private function ring(string $ring): string
    {
        return preg_replace('/^[A-Za-z]{1,3}\s+/', '', trim($ring));
    }

    /** Read a CSV file with a header row; returns null if a required column is missing. */
    private function readCsv(string $path, array $required): ?array
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $header = $header ? array_map(fn ($h) => trim(ltrim($h, "\xEF\xBB\xBF")), $header) : [];

        $missing = array_diff($required, $header);
        if ($missing) {
            $this->error(basename($path) . ' is missing column(s): ' . implode(', ', $missing));
            fclose($handle);
            return null;
        }

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if ($data === [null] || count(array_filter($data, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }
            $data = array_pad($data, count($header), '');
            $rows[] = array_combine($header, array_slice($data, 0, count($header)));
        }
        fclose($handle);

        return $rows;
    }
}
