<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\Pengguna;
use App\Services\EksporExcel;
use App\Services\LaporanKpiService;
use App\Support\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Laporan KPI per semester, dipakai admin (semua departemen) dan kepala unit (hanya departemennya).
 * Cakupan departemen kepala unit ditentukan di server dan tidak bisa diubah lewat parameter.
 */
class LaporanKpiController extends Controller
{
    public function index(Request $request, LaporanKpiService $laporan)
    {
        $konteks = $this->konteks($request, $laporan);

        return view('laporan.index', $konteks + [
            'departemen' => Departemen::orderBy('nama_dept')->get(),
        ]);
    }

    public function cetak(Request $request, LaporanKpiService $laporan)
    {
        return view('laporan.cetak', $this->konteks($request, $laporan));
    }

    public function excel(Request $request, LaporanKpiService $laporan)
    {
        $k = $this->konteks($request, $laporan);
        if ($k['pilihan'] === null) {
            return back()->with('galat', 'Belum ada periode untuk dibuat laporannya.');
        }

        $semester = (int) $k['pilihan']['semester'];
        $tahun = (string) $k['pilihan']['tahun'];
        $tahunSingkat = substr($tahun, -2);
        $isSem1 = $semester === 1;

        $labelSemester = $isSem1 ? 'SEMESTER 1' : 'SEMESTER 2';
        $rentangBulan = $isSem1 ? 'JANUARI - JUNI' : 'JULI - DESEMBER';
        $bulanList = $isSem1
            ? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun']
            : ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $namaDeptKapital = Str::upper((string) ($k['namaDept'] ?? ''));
        $subjudulKriteria = ($k['unit'] && $namaDeptKapital !== '' && $namaDeptKapital !== 'SEMUA DEPARTEMEN')
            ? "BULAN {$rentangBulan} TAHUN {$tahun} · {$namaDeptKapital}"
            : "BULAN {$rentangBulan} TAHUN {$tahun}";

        $subjudulAkhir = ($k['unit'] && $namaDeptKapital !== '' && $namaDeptKapital !== 'SEMUA DEPARTEMEN')
            ? "{$rentangBulan} · {$namaDeptKapital}"
            : $rentangBulan;

        $kriteriaKolom = [
            'Alpha',
            'Izin Tidak Masuk Tanpa Prosedur',
            'Pelanggaran K3',
            'SP 1',
            'SP 2',
            'SP 3',
            'SP Pertama & Terakhir',
            'Surat Teguran',
            'Sakit Tanpa Surat Dokter',
            'Terlambat Tanpa Keterangan',
        ];

        $barisList = collect($k['data']['baris'] ?? []);

        // Sheet 1: Rekap Penilaian KPI Kedisiplinan
        $barisKriteria = [];
        foreach ($barisList as $i => $r) {
            $rekapKriteria = $r['kriteria'] ?? ($r['detail_kriteria'] ?? []);
            $baris = [
                $i + 1,
                $r['karyawan']->nama_karyawan ?? '-',
                $r['karyawan']->departemen->nama_dept ?? '-',
            ];
            foreach ($kriteriaKolom as $namaKriteria) {
                $slug = Str::slug($namaKriteria, '_');
                $baris[] = (int) ($rekapKriteria[$namaKriteria] ?? ($rekapKriteria[$slug] ?? 0));
            }
            $barisKriteria[] = $baris;
        }

        // Sheet 2: Rekap Total Penilaian Akhir KPI Individu
        $periodeHeaders = array_map(fn ($bln) => "{$bln}-{$tahunSingkat}", $bulanList);
        $barisAkhir = [];
        foreach ($barisList as $i => $r) {
            $bulanan = $r['bulanan'] ?? ($r['periode_bulanan'] ?? []);
            $nilaiAkhir = $r['semester'] ?? ($r['total_akhir'] ?? null);

            $baris = [
                $i + 1,
                $r['karyawan']->nama_karyawan ?? '-',
                $r['karyawan']->departemen->nama_dept ?? '-',
                $nilaiAkhir !== null ? Format::nilai($nilaiAkhir) : '-',
            ];

            foreach ($bulanList as $idxBln => $bln) {
                $valBln = is_array($bulanan)
                    ? ($bulanan[$idxBln + 1] ?? ($bulanan[strtolower($bln)] ?? ($bulanan["{$bln}-{$tahunSingkat}"] ?? null)))
                    : null;
                $baris[] = $valBln !== null ? Format::nilai($valBln) : '-';
            }

            $barisAkhir[] = $baris;
        }

        $biner = EksporExcel::buatMultiSheet([
            [
                'judul' => "REKAP PENILAIAN KPI KEDISIPLINAN {$labelSemester}",
                'subjudul' => $subjudulKriteria,
                'kepala' => array_merge(['No', 'Nama Karyawan', 'Bagian'], $kriteriaKolom),
                'grupHeader' => [
                    [
                        'label' => 'Kriteria Jumlah Penilaian Kedisiplinan Karyawan',
                        'mulaiKolom' => 4,
                        'jumlahKolom' => count($kriteriaKolom),
                    ],
                ],
                'baris' => $barisKriteria,
                'rataTengahKolom' => range(1, 3 + count($kriteriaKolom)),
            ],
            [
                'judul' => "REKAP TOTAL PENILAIAN AKHIR KPI INDIVIDU {$labelSemester} TAHUN {$tahun}",
                'subjudul' => $subjudulAkhir,
                'kepala' => array_merge(
                    ['No', 'Nama Karyawan', 'Bagian', "Total Penilaian Akhir Individu Semester {$semester}"],
                    $periodeHeaders
                ),
                'grupHeader' => [
                    [
                        'label' => 'PERIODE',
                        'mulaiKolom' => 5,
                        'jumlahKolom' => count($periodeHeaders),
                    ],
                ],
                'baris' => $barisAkhir,
                'rataTengahKolom' => [1],
                'rataKananKolom' => range(4, 4 + count($periodeHeaders)),
            ],
        ]);

        $nama = "laporan-kpi-{$tahun}-semester-{$semester}.xlsx";

        return response()->streamDownload(function () use ($biner) {
            echo $biner;
        }, $nama, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** @return array<string,mixed> */
    private function konteks(Request $request, LaporanKpiService $laporan): array
    {
        $pengguna = $request->user();
        $unit = $pengguna->role === Pengguna::ROLE_KEPALA_UNIT;
        $awalan = $unit ? 'unit' : 'admin';

        if ($unit) {
            $idDept = $pengguna->karyawan?->id_dept;
            abort_if($idDept === null, 403);
        } else {
            $idDept = $request->filled('dept') ? (string) $request->input('dept') : null;
        }

        $semesterPilihan = $laporan->daftarSemester();
        $pilihan = null;
        foreach ($semesterPilihan as $s) {
            if ($s['nilai'] === (string) $request->input('semester')) {
                $pilihan = $s;
            }
        }
        $pilihan ??= $semesterPilihan[0] ?? null;

        $data = $pilihan
            ? $laporan->semester($pilihan['tahun'], $pilihan['semester'], $idDept)
            : ['baris' => [], 'rata' => null, 'tertinggi' => null, 'terendah' => null, 'jumlah' => 0];

        $namaDept = $idDept ? (Departemen::find($idDept)?->nama_dept ?? '-') : 'Semua Departemen';

        return [
            'awalan' => $awalan,
            'unit' => $unit,
            'idDept' => $idDept,
            'namaDept' => $namaDept,
            'semesterPilihan' => $semesterPilihan,
            'pilihan' => $pilihan,
            'data' => $data,
            'dicetak' => Format::tanggal(now()),
        ];
    }
}