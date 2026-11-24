<?php

namespace App\Http\Controllers\Saya;

use App\Http\Controllers\Concerns\DaftarLembur;
use App\Http\Controllers\Concerns\LingkupKaryawan;
use App\Http\Controllers\Controller;
use App\Models\RencanaLembur;

/** Karyawan melihat status rencana lembur yang melibatkan dirinya. */
class LemburController extends Controller
{
    use DaftarLembur, LingkupKaryawan;

    public function index()
    {
        $saya = $this->karyawanSaya();

        $daftar = RencanaLembur::with(['detail', 'departemen'])
            ->whereHas('detail', fn ($q) => $q->where('id_karyawan', $saya->id_karyawan))
            ->where('status_rencana', '!=', RencanaLembur::DRAF)
            ->orderByDesc('tanggal_lembur')
            ->paginate(15);

        $terbaru = $daftar->first();
        if ($terbaru) {
            $terbaru->load(['pengaju', 'persetujuan.pengguna.karyawan']);
        }

        return view('saya.lembur.index', ['daftar' => $daftar, 'saya' => $saya, 'terbaru' => $terbaru]);
    }

    public function show(RencanaLembur $rencana)
    {
        $saya = $this->karyawanSaya();
        abort_unless($rencana->status_rencana !== RencanaLembur::DRAF && $rencana->detail()->where('id_karyawan', $saya->id_karyawan)->exists(), 404);

        return view('saya.lembur.show', ['rencana' => $this->muatDetailLembur($rencana), 'saya' => $saya]);
    }
}
