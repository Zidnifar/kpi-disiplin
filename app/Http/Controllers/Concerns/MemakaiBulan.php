<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Illuminate\Http\Request;

/** Pembantu untuk filter bulan (format YYYY-MM) pada halaman daftar. */
trait MemakaiBulan
{
    protected function bulanDipilih(Request $request, ?string $bawaan = null): string
    {
        $bulan = (string) $request->input('bulan');
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) {
            return $bulan;
        }

        return $bawaan ?? now()->format('Y-m');
    }

    /** @return array{0:string,1:string} tanggal awal dan akhir bulan (Y-m-d) */
    protected function rentangBulan(string $bulan): array
    {
        $awal = Carbon::parse($bulan . '-01')->startOfMonth();

        return [$awal->toDateString(), $awal->copy()->endOfMonth()->toDateString()];
    }

    /** Bulan dari data terbaru (YYYY-MM) untuk nilai bawaan filter, atau null bila belum ada data. */
    protected function bulanDariTanggal(mixed $tanggal): ?string
    {
        return $tanggal ? substr((string) Carbon::parse($tanggal)->toDateString(), 0, 7) : null;
    }
}
