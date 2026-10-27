<?php

namespace Tests\Unit;

use App\Models\Pigeon;
use PHPUnit\Framework\TestCase;

class RingTest extends TestCase
{
    public function test_ring_formats_from_csv_and_oneloftrace_become_the_same(): void
    {
        $this->assertSame('24-8077898', Pigeon::normaliseRing('NL 24-8077898'));
        $this->assertSame('26-8349701', Pigeon::normaliseRing('NL26-8349701'));
        $this->assertSame('26-8349995', Pigeon::normaliseRing('8349995/26'));
        $this->assertSame('26-8350148', Pigeon::normaliseRing('8350148/26 (R1)'));
        $this->assertSame('26-8349977', Pigeon::normaliseRing(' 26-8349977 '));
        $this->assertSame('NL-2023-1001', Pigeon::normaliseRing('NL-2023-1001'));
    }

    public function test_hatch_year_is_read_from_both_ring_styles(): void
    {
        $this->assertSame(2026, (new Pigeon(['ring_number' => '26-8349977']))->hatchYear());
        $this->assertSame(2026, (new Pigeon(['ring_number' => 'NL-2026-0011']))->hatchYear());
        $this->assertNull((new Pigeon(['ring_number' => 'unknown']))->hatchYear());
    }
}
