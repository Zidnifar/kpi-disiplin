<?php

namespace App\Support;

use Carbon\Carbon;

/** Pemformat tampilan: tanggal Indonesia, nilai dengan koma desimal, jam, dan warna lencana status. */
final class Format
{
    /** 90 -> "90", 81.5 -> "81,5", null -> "—". */
    public static function nilai(?float $nilai): string
    {
        if ($nilai === null) {
            return '—';
        }

        return rtrim(rtrim(number_format($nilai, 2, ',', ''), '0'), ',');
    }

    public static function tanggal(mixed $tanggal): string
    {
        return $tanggal ? Carbon::parse($tanggal)->translatedFormat('d M Y') : '—';
    }

    public static function bulan(mixed $tanggal): string
    {
        return $tanggal ? Carbon::parse($tanggal)->translatedFormat('F Y') : '—';
    }

    public static function jam(?string $jam): string
    {
        return $jam ? substr($jam, 0, 5) : '—';
    }

    public static function rentangJam(?string $mulai, ?string $selesai): string
    {
        return self::jam($mulai) . ' – ' . self::jam($selesai);
    }

    /** 12 -> "12 jam", 7.5 -> "7,5 jam". */
    public static function jumlahJam(?float $jam): string
    {
        if ($jam === null) {
            return '—';
        }

        return rtrim(rtrim(number_format($jam, 1, ',', ''), '0'), ',') . ' jam';
    }

    /** Selisih bertanda: +1 jam, -1 jam, 0. */
    public static function selisihJam(float $selisih): string
    {
        if (abs($selisih) < 0.05) {
            return '0';
        }

        return ($selisih > 0 ? '+' : '-') . rtrim(rtrim(number_format(abs($selisih), 1, ',', ''), '0'), ',') . ' jam';
    }

    /** Kelas CSS lencana berdasarkan kata kunci pada status. */
    public static function kelasStatus(string $status): string
    {
        $s = mb_strtolower($status);

        return match (true) {
            str_contains($s, 'tolak'), str_contains($s, 'kembali'), str_contains($s, 'alpa'),
            str_contains($s, 'nonaktif'), str_contains($s, 'bermasalah'), str_contains($s, 'kurang') => 'lencana-merah',
            str_contains($s, 'menunggu'), str_contains($s, 'draf'), str_contains($s, 'berjalan'),
            str_contains($s, 'belum'), str_contains($s, 'perlu'), str_contains($s, 'cukup'),
            str_contains($s, 'terlambat'), str_contains($s, 'awal') => 'lencana-kuning',
            str_contains($s, 'baik') && ! str_contains($s, 'sangat') => 'lencana-biru',
            str_contains($s, 'sudah'), str_contains($s, 'selesai'), str_contains($s, 'setuju'),
            str_contains($s, 'valid'), str_contains($s, 'verifikasi'), str_contains($s, 'aktif'),
            str_contains($s, 'hadir'), str_contains($s, 'sangat baik'), str_contains($s, 'diperiksa'),
            str_contains($s, 'diketahui') => 'lencana-hijau',
            default => 'lencana-abu',
        };
    }

    /** Bagian depan kata kunci LIKE agar karakter % dan _ dari pengguna tidak ikut menjadi wildcard. */
    public static function like(string $teks): string
    {
        return '%' . addcslashes($teks, '%_\\') . '%';
    }
}
