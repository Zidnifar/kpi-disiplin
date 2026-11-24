<?php

declare(strict_types=1);

namespace App\Domain\Attendance;

/**
 * Aturan absensi (murni PHP).
 *
 *  - Terlambat      : masuk lewat jam_mulai + toleransi (toleransi 0 = lewat jam mulai shift).
 *  - Pulang awal    : pulang pada/ sebelum (jam_selesai - batas_pulang_awal), mis. 1 jam lebih awal.
 *  - Cuti, izin resmi, sakit dengan SKD tidak mengurangi poin.
 */
final class AttendanceRules
{
    public const HADIR = 'Hadir';
    public const TERLAMBAT = 'Terlambat';
    public const PULANG_AWAL = 'Pulang Awal';
    public const TERLAMBAT_PULANG_AWAL = 'Terlambat dan Pulang Awal';
    public const PERLU_DICEK = 'Perlu Dicek';
    public const ALPA = 'Alpa';
    public const CUTI = 'Cuti';
    public const IZIN_RESMI = 'Izin Resmi';
    public const SAKIT_SKD = 'Sakit dengan SKD';
    public const SAKIT_TANPA_SKD = 'Sakit Tanpa SKD';
    public const IZIN_TANPA_PROSEDUR = 'Izin Tanpa Prosedur';

    /** Mengubah "H:i" atau "H:i:s" menjadi detik sejak tengah malam. */
    public static function detik(string $waktu): int
    {
        $bagian = explode(':', $waktu);

        return ((int) $bagian[0]) * 3600 + ((int) ($bagian[1] ?? 0)) * 60 + (int) ($bagian[2] ?? 0);
    }

    /**
     * @return array{status:string, terlambat:bool, pulang_awal:bool, masalah:array<int,string>}
     */
    public static function evaluasi(
        ?string $masuk,
        ?string $pulang,
        string $jamMulai,
        string $jamSelesai,
        int $toleransiMenit = 0,
        int $batasPulangAwalMenit = 60
    ): array {
        $masalah = [];
        $terlambat = false;
        $pulangAwal = false;

        if ($masuk === null) {
            $masalah[] = 'Jam masuk kosong';
        } else {
            $terlambat = self::detik($masuk) > self::detik($jamMulai) + $toleransiMenit * 60;
        }

        if ($pulang === null) {
            $masalah[] = 'Jam pulang kosong';
        } else {
            $pulangAwal = self::detik($pulang) <= self::detik($jamSelesai) - $batasPulangAwalMenit * 60;
        }

        if ($masalah !== []) {
            $status = self::PERLU_DICEK;
        } elseif ($terlambat && $pulangAwal) {
            $status = self::TERLAMBAT_PULANG_AWAL;
        } elseif ($terlambat) {
            $status = self::TERLAMBAT;
        } elseif ($pulangAwal) {
            $status = self::PULANG_AWAL;
        } else {
            $status = self::HADIR;
        }

        return [
            'status' => $status,
            'terlambat' => $terlambat,
            'pulang_awal' => $pulangAwal,
            'masalah' => $masalah,
        ];
    }

    /**
     * Pelanggaran (kode indikator => jumlah kejadian) untuk status akhir kehadiran.
     * Status "Perlu Dicek" belum menghasilkan pelanggaran sebelum dikonfirmasi.
     * Terlambat dan pulang awal pada hari yang sama dihitung dua kali (asumsi: perlu dikonfirmasi ke HR).
     *
     * @return array<string,int>
     */
    public static function pelanggaranDariStatus(string $status): array
    {
        return match ($status) {
            self::TERLAMBAT, self::PULANG_AWAL => ['TMK' => 1],
            self::TERLAMBAT_PULANG_AWAL => ['TMK' => 2],
            self::ALPA => ['A' => 1],
            self::IZIN_TANPA_PROSEDUR => ['ITMTP' => 1],
            self::SAKIT_TANPA_SKD => ['STSK' => 1],
            default => [],
        };
    }

    /** Status yang tidak dihitung sebagai pelanggaran. */
    public static function statusTidakDihitung(): array
    {
        return [self::CUTI, self::IZIN_RESMI, self::SAKIT_SKD];
    }

    /** Status yang boleh ditetapkan admin saat memperbaiki data (tanpa "Perlu Dicek"). */
    public static function statusPilihan(): array
    {
        return [
            self::HADIR,
            self::TERLAMBAT,
            self::PULANG_AWAL,
            self::TERLAMBAT_PULANG_AWAL,
            self::ALPA,
            self::CUTI,
            self::IZIN_RESMI,
            self::SAKIT_SKD,
            self::SAKIT_TANPA_SKD,
            self::IZIN_TANPA_PROSEDUR,
        ];
    }

    /**
     * Pengelompokan status untuk rekap: hadir | terlambat | izin | alpa | cek.
     * Pulang awal dihitung hadir; terlambat + pulang awal dihitung terlambat.
     */
    public static function kelompok(string $status): string
    {
        return match ($status) {
            self::HADIR, self::PULANG_AWAL => 'hadir',
            self::TERLAMBAT, self::TERLAMBAT_PULANG_AWAL => 'terlambat',
            self::CUTI, self::IZIN_RESMI, self::SAKIT_SKD, self::SAKIT_TANPA_SKD, self::IZIN_TANPA_PROSEDUR => 'izin',
            self::ALPA => 'alpa',
            default => 'cek',
        };
    }
}
