<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Kpi\KpiCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class KpiCalculatorTest extends TestCase
{
    public function test_nilai_triwulan_contoh_andi(): void
    {
        // A x1 (5) + ITMTP x1 (2) + TMK x3 (1) = 10 poin
        $items = [
            ['frekuensi' => 1, 'bobot_poin' => 5],
            ['frekuensi' => 1, 'bobot_poin' => 2],
            ['frekuensi' => 3, 'bobot_poin' => 1],
        ];
        $total = KpiCalculator::totalPengurang($items);

        $this->assertSame(10, $total);
        $this->assertSame(90.0, KpiCalculator::nilaiTriwulan($total));
    }

    public function test_tanpa_pelanggaran_nilai_100(): void
    {
        $this->assertSame(100.0, KpiCalculator::nilaiTriwulan(KpiCalculator::totalPengurang([])));
    }

    public function test_nilai_dibatasi_nol_secara_bawaan(): void
    {
        $this->assertSame(0.0, KpiCalculator::nilaiTriwulan(120));
        $this->assertSame(-20.0, KpiCalculator::nilaiTriwulan(120, 100, false));
    }

    public function test_frekuensi_negatif_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        KpiCalculator::totalPengurang([['frekuensi' => -1, 'bobot_poin' => 5]]);
    }

    public function test_nilai_semester_rata_rata_dua_triwulan(): void
    {
        $this->assertSame(89.0, KpiCalculator::nilaiSemester(90.0, 88.0));
        $this->assertSame(87.5, KpiCalculator::nilaiSemester(90.0, 85.0));
    }

    public function test_semester_dari_triwulan(): void
    {
        $this->assertSame(1, KpiCalculator::semesterDariTriwulan(1));
        $this->assertSame(1, KpiCalculator::semesterDariTriwulan(2));
        $this->assertSame(2, KpiCalculator::semesterDariTriwulan(3));
        $this->assertSame(2, KpiCalculator::semesterDariTriwulan(4));
    }

    public function test_triwulan_tidak_valid_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        KpiCalculator::semesterDariTriwulan(5);
    }

    public function test_pasangan_triwulan_dalam_semester(): void
    {
        $this->assertSame([1, 2], KpiCalculator::triwulanDalamSemester(1));
        $this->assertSame([3, 4], KpiCalculator::triwulanDalamSemester(2));
    }

    public function test_predikat_rentang_kontinu(): void
    {
        $daftar = [
            ['nama' => 'Sangat Baik', 'min' => 90.0, 'max' => 100.0],
            ['nama' => 'Baik', 'min' => 80.0, 'max' => 89.99],
            ['nama' => 'Cukup', 'min' => 70.0, 'max' => 79.99],
            ['nama' => 'Kurang', 'min' => 0.0, 'max' => 69.99],
        ];
        $harapan = [
            [100.0, 'Sangat Baik'], [90.0, 'Sangat Baik'], [89.5, 'Baik'], [80.0, 'Baik'],
            [79.99, 'Cukup'], [70.0, 'Cukup'], [69.99, 'Kurang'], [0.0, 'Kurang'],
        ];
        foreach ($harapan as [$nilai, $nama]) {
            $this->assertSame($nama, KpiCalculator::predikat($nilai, $daftar)['nama'], "nilai $nilai");
        }
    }
}
