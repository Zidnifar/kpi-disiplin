<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Concerns\LingkupKaryawan;
use App\Http\Controllers\Controller;
use App\Services\LaporanKpiService;
use Illuminate\Http\Request;

/** Ringkasan KPI unit: rata-rata, sebaran kategori, rata-rata per triwulan, dan 5 peringkat teratas. */
class RekapKpiController extends Controller
{
    use LingkupKaryawan;

    public function index(Request $request, LaporanKpiService $laporan)
    {
        $saya = $this->karyawanSaya();
        $semesterPilihan = $laporan->daftarSemester();
        $pilihan = null;
        foreach ($semesterPilihan as $s) {
            if ($s['nilai'] === (string) $request->input('semester')) {
                $pilihan = $s;
            }
        }
        $pilihan ??= $semesterPilihan[0] ?? null;

        $data = $pilihan
            ? $laporan->semester($pilihan['tahun'], $pilihan['semester'], $saya->id_dept)
            : ['baris' => [], 'rata' => null, 'tertinggi' => null, 'terendah' => null, 'jumlah' => 0];

        $sebaran = [];
        $jumlahTw = ['tw1' => [], 'tw2' => []];
        foreach ($data['baris'] as $r) {
            if ($r['predikat'] !== null) {
                $sebaran[$r['predikat']] = ($sebaran[$r['predikat']] ?? 0) + 1;
            }
            foreach (['tw1', 'tw2'] as $k) {
                if ($r[$k] !== null) {
                    $jumlahTw[$k][] = $r[$k];
                }
            }
        }
        $rataTw = [];
        foreach ($jumlahTw as $k => $nilai) {
            $rataTw[$k] = $nilai === [] ? null : round(array_sum($nilai) / count($nilai), 1);
        }

        $peringkat = array_slice(array_filter($data['baris'], fn ($r) => $r['semester'] !== null), 0, 5);

        return view('unit.kpi.rekap', compact('pilihan', 'semesterPilihan', 'data', 'sebaran', 'rataTw', 'peringkat'));
    }
}
