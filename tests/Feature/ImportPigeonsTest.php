<?php

namespace Tests\Feature;

use App\Models\Pigeon;
use App\Models\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportPigeonsTest extends TestCase
{
    use RefreshDatabase;

    // Folder (relative to the project) with small CSV files for the importer
    private const DIR = 'storage/framework/testing/import';

    protected function setUp(): void
    {
        parent::setUp();

        File::ensureDirectoryExists(base_path(self::DIR));
        File::put(base_path(self::DIR . '/races.csv'), "name,country,season\nMira One Loft Race 2026,Portugal,2026\n");
        File::put(base_path(self::DIR . '/race_entries.csv'), "ring_number,sex,colour,father_ring,mother_ring,race\n"
            . "NL 26-8349995,cock,blauw,24-8077901,24-8078075,Mira One Loft Race 2026\n");
        File::put(base_path(self::DIR . '/results.csv'), "ring_number,race,stage_type,stage_name,participants,position\n"
            . "26-8349995,Mira One Loft Race 2026, Final ,Final (310 km),850,97\n");
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path(self::DIR));

        parent::tearDown();
    }

    public function test_import_creates_pigeon_parents_and_result(): void
    {
        $this->artisan('import:pigeons', ['--path' => self::DIR])->assertSuccessful();

        $this->assertSame(3, Pigeon::count()); // youngster + father + mother
        $this->assertSame(97, Result::first()?->position); // " Final " is read as final
    }

    public function test_reimport_keeps_lost_status(): void
    {
        $this->artisan('import:pigeons', ['--path' => self::DIR])->assertSuccessful();
        Pigeon::where('ring_number', '26-8349995')->update(['status' => 'lost', 'status_date' => '2026-09-01']);

        $this->artisan('import:pigeons', ['--path' => self::DIR])->assertSuccessful();

        $this->assertSame('lost', Pigeon::where('ring_number', '26-8349995')->value('status'));
    }
}
