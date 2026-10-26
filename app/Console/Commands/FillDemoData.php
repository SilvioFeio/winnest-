<?php

namespace App\Console\Commands;

use App\Models\FertilityRecord;
use App\Models\Pair;
use App\Models\Pigeon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * DEMO ONLY: fills fertility records and survival status with made-up numbers
 * so the ranking and dashboard can be shown before Gerard supplies the real data.
 * These numbers are NOT real. Use --reset to remove them again.
 */
class FillDemoData extends Command
{
    protected $signature = 'demo:fill {--reset : Remove the demo data again} {--seed=2026 : Same seed gives the same numbers}';

    protected $description = 'DEMO ONLY: fill fertility and survival with made-up data (use --reset to remove)';

    public function handle(): int
    {
        if ($this->option('reset')) {
            DB::transaction(function () {
                FertilityRecord::query()->delete();
                Pigeon::whereNotNull('parent_pair_id')->update(['status' => 'alive', 'status_date' => null]);
            });
            $this->info('Demo fertility and survival data removed.');
            return self::SUCCESS;
        }

        mt_srand((int) $this->option('seed'));

        $pairs = Pair::withCount('youngsters')->get();
        if ($pairs->isEmpty()) {
            $this->error('No pairs found. Run "php artisan import:pigeons" first.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($pairs) {
            foreach ($pairs as $pair) {
                // Always at least as many weaned youngsters as youngsters we know of
                $youngsters = $pair->youngsters_count;
                $eggs = mt_rand(max(6, $youngsters + 2), max(10, $youngsters + 4));
                $fertile = (int) round($eggs * mt_rand(65, 100) / 100);
                $hatched = max(min($fertile, $eggs) - mt_rand(0, 1), $youngsters);
                $weaned = max($youngsters, $hatched - mt_rand(0, 1));

                FertilityRecord::updateOrCreate(
                    ['pair_id' => $pair->id],
                    [
                        'eggs_laid' => $eggs,
                        'fertile_eggs' => max($fertile, $hatched),
                        'hatched' => $hatched,
                        'weaned' => min($weaned, $hatched),
                    ]
                );
            }

            // Roughly 1 in 8 youngsters is marked as lost
            Pigeon::whereNotNull('parent_pair_id')->get()->each(function (Pigeon $pigeon) {
                if (mt_rand(1, 8) === 1) {
                    $pigeon->update([
                        'status' => 'lost',
                        'status_date' => now()->setDate(2026, mt_rand(8, 10), mt_rand(1, 28))->toDateString(),
                    ]);
                } else {
                    $pigeon->update(['status' => 'alive', 'status_date' => null]);
                }
            });
        });

        $this->warn('DEMO DATA: fertility and survival are made up, not real.');
        $this->info($pairs->count() . ' fertility records created. Remove with: php artisan demo:fill --reset');

        return self::SUCCESS;
    }
}
