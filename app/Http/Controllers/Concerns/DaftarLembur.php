<?php

namespace App\Http\Controllers\Concerns;

use App\Models\DetailLembur;
use App\Models\RencanaLembur;
use App\Support\Format;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** Pembantu daftar rencana lembur untuk halaman Kepala Produksi, Operasional Manager, dan General Manager. */
trait DaftarLembur
{
    /** Filter pencarian nomor pengajuan dan unit, plus relasi yang dibutuhkan tabel. */
    protected function filterLembur(Builder $query, Request $request): Builder
    {
        return $query
            ->with(['departemen', 'pengaju', 'detail'])
            ->when($request->filled('q'), fn ($q) => $q->where('no_pengajuan', 'like', Format::like((string) $request->input('q'))))
            ->when($request->filled('unit'), fn ($q) => $q->where('id_dept', $request->input('unit')))
            ->orderByDesc('tanggal_lembur')
            ->orderByDesc('no_pengajuan');
    }

    /** Total jam rencana lembur yang sudah disetujui pada bulan berjalan. */
    protected function jamDisetujuiBulanIni(): float
    {
        return (float) DetailLembur::whereHas('rencana', function ($q) {
            $q->whereIn('status_rencana', [RencanaLembur::DISETUJUI, RencanaLembur::SELESAI])
                ->whereBetween('tanggal_lembur', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);
        })->sum('jam_rencana');
    }

    protected function muatDetailLembur(RencanaLembur $rencana): RencanaLembur
    {
        return $rencana->load(['departemen', 'pengaju', 'shift', 'detail.karyawan', 'persetujuan.pengguna.karyawan']);
    }
}
