<?php

namespace Tests\Unit;

use App\Support\Rupiah;
use PHPUnit\Framework\TestCase;

class RupiahTest extends TestCase
{
    public function test_formats_full_rupiah(): void
    {
        $this->assertSame('Rp1.050.000', Rupiah::penuh(1050000));
        $this->assertSame('Rp0', Rupiah::penuh(0));
        $this->assertSame('—', Rupiah::penuh(null));
    }

    public function test_formats_compact_rupiah(): void
    {
        $this->assertSame('Rp1,05 jt', Rupiah::ringkas(1050000));
        $this->assertSame('Rp950 rb', Rupiah::ringkas(950000));
        $this->assertSame('Rp1,2 jt', Rupiah::ringkas(1200000));
        $this->assertSame('Rp1 jt', Rupiah::ringkas(1000000));
        $this->assertSame('—', Rupiah::ringkas(null));
    }
}
