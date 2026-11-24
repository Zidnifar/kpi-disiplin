<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HasilKpi;
use App\Models\PeriodePenilaian;
use App\Services\KpiService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HitungKpiController extends Controller
{
    public function show(PeriodePenilaian $periode, KpiService $kpi)
    {
        return view('admin.kpi.hitung', [
            'periode' => $periode,
            'baris' => $kpi->pratinjau($periode),
            'belumSelesai' => $kpi->kesalahanBelumSelesai($periode),
            'tervalidasi' => HasilKpi::where('id_periode', $periode->id_periode)
                ->where('status_validasi', KpiService::VALIDASI_OK)->count(),
        ]);
    }

    public function proses(Request $request, PeriodePenilaian $periode, KpiService $kpi): RedirectResponse
    {
        try {
            $hasil = $kpi->hitungTriwulan($periode->id_periode, $request->boolean('paksa'));
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        $pesan = "Perhitungan selesai: {$hasil['dihitung']} karyawan dihitung";
        if ($hasil['dilewati_tervalidasi'] > 0) {
            $pesan .= ", {$hasil['dilewati_tervalidasi']} dilewati karena sudah tervalidasi";
        }

        return redirect()->route('admin.kpi.hasil.index', ['periode' => $periode->id_periode])->with('sukses', $pesan . '.');
    }
}
