<?php

namespace Tests\Unit;

use App\Services\LemburService;
use PHPUnit\Framework\TestCase;

class LemburHitungJamTest extends TestCase
{
    public function test_hitung_jam_biasa(): void
    {
        $this->assertSame(3.0, LemburService::hitungJam('17:00:00', '20:00:00'));
        $this->assertSame(3.5, LemburService::hitungJam('17:00:00', '20:30:00'));
    }

    public function test_hitung_jam_melewati_tengah_malam(): void
    {
        $this->assertSame(4.0, LemburService::hitungJam('22:00:00', '02:00:00'));
    }

    public function test_hitung_jam_dibulatkan_satu_desimal(): void
    {
        $this->assertSame(1.3, LemburService::hitungJam('17:00:00', '18:20:00'));
    }
}
