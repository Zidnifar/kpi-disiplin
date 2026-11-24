<?php

declare(strict_types=1);

namespace App\Domain\Kpi;

use InvalidArgumentException;

/**
 * Aturan perhitungan KPI (murni PHP, tanpa database agar mudah diuji).
 *
 *  nilai_triwulan = nilai_awal (100) - SUM(frekuensi x bobot_poin)
 *  nilai_semester = (nilai triwulan pertama + nilai triwulan kedua) / 2
 *      Semester 1 = triwulan 1 & 2, Semester 2 = triwulan 3 & 4
 */
final class KpiCalculator
{
    public const NILAI_AWAL = 100;

    /**
     * @param array<int, array{frekuensi:int, bobot_poin:int}> $items
     */
    public static function totalPengurang(array $items): int
    {
        $total = 0;
        foreach ($items as $item) {
            if ($item['frekuensi'] < 0 || $item['bobot_poin'] < 0) {
                throw new InvalidArgumentException('Frekuensi dan bobot poin tidak boleh negatif.');
            }
            $total += $item['frekuensi'] * $item['bobot_poin'];
        }

        return $total;
    }

    /**
     * Nilai triwulan. Secara bawaan nilai tidak boleh di bawah 0 (asumsi: perlu dikonfirmasi ke HR).
     */
    public static function nilaiTriwulan(
        int $totalPengurang,
        int $nilaiAwal = self::NILAI_AWAL,
        bool $batasBawahNol = true
    ): float {
        $nilai = $nilaiAwal - $totalPengurang;
        if ($batasBawahNol && $nilai < 0) {
            $nilai = 0;
        }

        return (float) $nilai;
    }

    public static function nilaiSemester(float $triwulanPertama, float $triwulanKedua): float
    {
        return round(($triwulanPertama + $triwulanKedua) / 2, 2);
    }

    public static function semesterDariTriwulan(int $triwulan): int
    {
        if ($triwulan < 1 || $triwulan > 4) {
            throw new InvalidArgumentException('Triwulan harus 1 sampai 4.');
        }

        return (int) ceil($triwulan / 2);
    }

    /**
     * @return array{0:int, 1:int} pasangan triwulan pembentuk semester
     */
    public static function triwulanDalamSemester(int $semester): array
    {
        return match ($semester) {
            1 => [1, 2],
            2 => [3, 4],
            default => throw new InvalidArgumentException('Semester harus 1 atau 2.'),
        };
    }

    /**
     * Menentukan predikat. Rentang dibaca kontinu: predikat dengan nilai_min terbesar
     * yang masih <= nilai. Jadi 89,5 tetap masuk "Baik" (80 - 89,99) walau tidak ada celah ke 90.
     *
     * @param array<int, array{nama:string, min:float, max:float, id?:string}> $daftarPredikat
     * @return array{nama:string, min:float, max:float, id?:string}|null
     */
    public static function predikat(float $nilai, array $daftarPredikat): ?array
    {
        usort($daftarPredikat, static fn (array $a, array $b): int => $b['min'] <=> $a['min']);
        foreach ($daftarPredikat as $predikat) {
            if ($nilai >= $predikat['min']) {
                return $predikat;
            }
        }

        return null;
    }
}
