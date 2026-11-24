<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Concerns\LingkupKaryawan;
use App\Http\Controllers\Controller;
use App\Services\EksporExcel;
use App\Services\LaporanKpiService;
use App\Support\Format;
use Illuminate\Http\Request;

/** Nilai dan kategori KPI (per semester) seluruh karyawan di departemen kepala unit. */
class NilaiKpiController extends Controller
{
    use LingkupKaryawan;

    public function index(Request $request, LaporanKpiService $laporan)
    {
        [$pilihan, $semesterPilihan, $data] = $this->data($request, $laporan);

        $kata = (string) $request->input('q');
        $kategori = (string) $request->input('kategori');
        $baris = array_values(array_filter($data['baris'], function (array $r) use ($kata, $kategori) {
            if ($kata !== '' && ! str_contains(mb_strtolower($r['karyawan']->nama_karyawan . ' ' . ($r['karyawan']->nip ?? '')), mb_strtolower($kata))) {
                return false;
            }

            return $kategori === '' || $r['predikat'] === $kategori;
        }));

        $sebaran = [];
        foreach ($data['baris'] as $r) {
            if ($r['predikat'] !== null) {
                $sebaran[$r['predikat']] = ($sebaran[$r['predikat']] ?? 0) + 1;
            }
        }

        return view('unit.kpi.nilai', compact('pilihan', 'semesterPilihan', 'baris', 'sebaran'));
    }

    public function excel(Request $request, LaporanKpiService $laporan)
    {
        [$pilihan, , $data] = $this->data($request, $laporan);
        if ($pilihan === null) {
            return back()->with('galat', 'Belum ada periode.');
        }

        [$tw1, $tw2] = $pilihan['semester'] === 1 ? [1, 2] : [3, 4];
        $baris = [];
        foreach ($data['baris'] as $r) {
            $baris[] = [$r['karyawan']->nama_karyawan, $r['karyawan']->jabatan, $r['tw1'], $r['tw2'], $r['semester'], $r['predikat'] ?? ''];
        }
        $biner = EksporExcel::buat('Nilai KPI', ['Nama', 'Jabatan', "Triwulan {$tw1}", "Triwulan {$tw2}", "Semester {$pilihan['semester']}", 'Kategori'], $baris);

        return response()->streamDownload(function () use ($biner) {
            echo $biner;
        }, "nilai-kpi-{$pilihan['tahun']}-semester-{$pilihan['semester']}.xlsx", ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** @return array{0:?array<string,mixed>,1:array<int,array<string,mixed>>,2:array<string,mixed>} */
    private function data(Request $request, LaporanKpiService $laporan): array
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

        return [$pilihan, $semesterPilihan, $data];
    }
}
