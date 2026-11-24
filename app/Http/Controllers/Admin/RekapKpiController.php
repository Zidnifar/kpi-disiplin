<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\PeriodePenilaian;
use App\Services\EksporExcel;
use App\Services\LaporanKpiService;
use Illuminate\Http\Request;

class RekapKpiController extends Controller
{
    public function index(Request $request, LaporanKpiService $laporan)
    {
        [$tahun, $tw, $idDept, $tahunPilihan] = $this->parameter($request);

        return view('admin.kpi.rekap', [
            'tahun' => $tahun,
            'tw' => $tw,
            'idDept' => $idDept,
            'tahunPilihan' => $tahunPilihan,
            'departemen' => Departemen::orderBy('nama_dept')->get(),
            'baris' => $laporan->tahunan($tahun, $idDept),
            'rataDept' => $laporan->rataPerDepartemen($tahun, $tw),
        ]);
    }

    public function excel(Request $request, LaporanKpiService $laporan)
    {
        [$tahun, , $idDept] = $this->parameter($request);

        $baris = [];
        foreach ($laporan->tahunan($tahun, $idDept) as $r) {
            $baris[] = [
                $r['karyawan']->nama_karyawan,
                $r['karyawan']->nip ?? '',
                $r['karyawan']->departemen->nama_dept ?? '',
                $r['tw'][1], $r['tw'][2], $r['smt1'], $r['tw'][3], $r['tw'][4], $r['smt2'],
            ];
        }
        $biner = EksporExcel::buat("Rekap KPI {$tahun}", ['Nama', 'NIP', 'Departemen', 'TW 1', 'TW 2', 'Semester 1', 'TW 3', 'TW 4', 'Semester 2'], $baris);

        return response()->streamDownload(function () use ($biner) {
            echo $biner;
        }, "rekap-kpi-{$tahun}.xlsx", ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** @return array{0:int,1:int,2:?string,3:array<int,int>} */
    private function parameter(Request $request): array
    {
        $tahunPilihan = PeriodePenilaian::query()->distinct()->orderByDesc('tahun')->pluck('tahun')->map(fn ($t) => (int) $t)->all();
        $tahun = (int) $request->input('tahun', $tahunPilihan[0] ?? now()->year);
        $tw = (int) $request->input('tw', 0);
        if ($tw < 1 || $tw > 4) {
            $tw = (int) (PeriodePenilaian::where('tahun', $tahun)->whereHas('hasilKpi')->max('triwulan') ?: 1);
        }
        $idDept = $request->filled('dept') ? (string) $request->input('dept') : null;

        return [$tahun, $tw, $idDept, $tahunPilihan];
    }
}
