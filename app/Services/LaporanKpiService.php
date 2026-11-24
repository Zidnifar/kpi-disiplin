<?php

namespace App\Services;

use App\Domain\Kpi\KpiCalculator;
use App\Models\HasilKpi;
use App\Models\Karyawan;
use App\Models\NilaiSemester;
use App\Models\PeriodePenilaian;
use App\Models\PredikatNilai;

/**
 * Pembaca data KPI untuk rekap dan laporan. Hanya hasil berstatus "Tervalidasi" yang dianggap final,
 * jadi nilai yang belum divalidasi tidak muncul di rekap, laporan, maupun halaman karyawan.
 */
class LaporanKpiService
{
    /**
     * Laporan satu semester (dua triwulan).
     *
     * @return array{baris:array<int,array<string,mixed>>, rata:?float, tertinggi:?float, terendah:?float, jumlah:int}
     */
    public function semester(int $tahun, int $semester, ?string $idDept = null): array
    {
        [$tw1, $tw2] = KpiCalculator::triwulanDalamSemester($semester);

        $hasil = HasilKpi::with(['periode', 'detail.indikator'])
            ->where('status_validasi', KpiService::VALIDASI_OK)
            ->whereHas('periode', fn ($q) => $q->where('tahun', $tahun)->whereIn('triwulan', [$tw1, $tw2]))
            ->get()
            ->groupBy('id_karyawan');

        $nilaiSemester = NilaiSemester::with('predikat')
            ->where('tahun', $tahun)
            ->where('semester', $semester)
            ->get()
            ->keyBy('id_karyawan');

        $baris = [];
        foreach ($this->karyawan($idDept, $hasil->keys()->all()) as $k) {
            $milik = $hasil->get($k->id_karyawan, collect());
            $ambil = fn (int $tw) => $milik->first(fn (HasilKpi $h) => (int) $h->periode->triwulan === $tw)?->nilai_triwulan;
            $sem = $nilaiSemester->get($k->id_karyawan);

            $rekapKriteria = [];
            foreach ($milik as $h) {
                foreach ($h->detail ?? [] as $d) {
                    $namaIndikator = trim((string) ($d->indikator->nama_indikator ?? ($d->indikator->kode_indikator ?? '')));
                    if ($namaIndikator !== '') {
                        $rekapKriteria[$namaIndikator] = ($rekapKriteria[$namaIndikator] ?? 0) + (int) ($d->frekuensi ?? 1);
                    }
                }
            }

            $baris[] = [
                'karyawan' => $k,
                'tw1' => $ambil($tw1),
                'tw2' => $ambil($tw2),
                'semester' => $sem?->nilai_semester,
                'predikat' => $sem?->predikat?->nama_predikat,
                'kriteria' => $rekapKriteria,
            ];
        }

        usort($baris, function (array $a, array $b) {
            if ($a['semester'] === null && $b['semester'] === null) {
                return strcmp($a['karyawan']->nama_karyawan, $b['karyawan']->nama_karyawan);
            }
            if ($a['semester'] === null) {
                return 1;
            }
            if ($b['semester'] === null) {
                return -1;
            }

            return $b['semester'] <=> $a['semester'];
        });

        return ['baris' => $baris] + $this->statistik(array_column($baris, 'semester'));
    }

    /**
     * Rekap satu tahun: nilai tiap triwulan dan semester per karyawan.
     *
     * @return array<int,array<string,mixed>>
     */
    public function tahunan(int $tahun, ?string $idDept = null): array
    {
        $hasil = HasilKpi::with('periode')
            ->where('status_validasi', KpiService::VALIDASI_OK)
            ->whereHas('periode', fn ($q) => $q->where('tahun', $tahun))
            ->get()
            ->groupBy('id_karyawan');
        $semester = NilaiSemester::where('tahun', $tahun)->get()->groupBy('id_karyawan');

        $baris = [];
        foreach ($this->karyawan($idDept, $hasil->keys()->all()) as $k) {
            $milik = $hasil->get($k->id_karyawan, collect());
            $tw = [];
            for ($i = 1; $i <= 4; $i++) {
                $tw[$i] = $milik->first(fn (HasilKpi $h) => (int) $h->periode->triwulan === $i)?->nilai_triwulan;
            }
            $sem = $semester->get($k->id_karyawan, collect());
            $baris[] = [
                'karyawan' => $k,
                'tw' => $tw,
                'smt1' => $sem->firstWhere('semester', 1)?->nilai_semester,
                'smt2' => $sem->firstWhere('semester', 2)?->nilai_semester,
            ];
        }

        return $baris;
    }

    /**
     * Rata-rata nilai per departemen untuk satu triwulan.
     *
     * @return array<int,array{dept:string,rata:float,jumlah:int}>
     */
    public function rataPerDepartemen(int $tahun, int $triwulan): array
    {
        $hasil = HasilKpi::with('karyawan.departemen')
            ->where('status_validasi', KpiService::VALIDASI_OK)
            ->whereHas('periode', fn ($q) => $q->where('tahun', $tahun)->where('triwulan', $triwulan))
            ->get()
            ->groupBy(fn (HasilKpi $h) => $h->karyawan?->departemen?->nama_dept ?? 'Tanpa departemen');

        $hasilAkhir = [];
        foreach ($hasil as $nama => $daftar) {
            $hasilAkhir[] = ['dept' => $nama, 'rata' => round((float) $daftar->avg('nilai_triwulan'), 1), 'jumlah' => $daftar->count()];
        }
        usort($hasilAkhir, fn ($a, $b) => strcmp($a['dept'], $b['dept']));

        return $hasilAkhir;
    }

    /**
     * Ringkasan satu periode: rata-rata dan jumlah karyawan per predikat (semua hasil, termasuk yang belum divalidasi).
     *
     * @return array{rata:?float, jumlah:int, sebaran:array<string,int>}
     */
    public function ringkasanPeriode(string $idPeriode, ?string $idDept = null): array
    {
        $hasil = HasilKpi::with('predikat')
            ->where('id_periode', $idPeriode)
            ->when($idDept, fn ($q) => $q->whereHas('karyawan', fn ($k) => $k->where('id_dept', $idDept)))
            ->get();

        $sebaran = [];
        foreach (PredikatNilai::where('status_predikat', 'Aktif')->orderByDesc('nilai_min')->get() as $p) {
            $sebaran[$p->nama_predikat] = 0;
        }
        foreach ($hasil as $h) {
            $nama = $h->predikat?->nama_predikat ?? 'Tanpa predikat';
            $sebaran[$nama] = ($sebaran[$nama] ?? 0) + 1;
        }

        return [
            'rata' => $hasil->isEmpty() ? null : round((float) $hasil->avg('nilai_triwulan'), 1),
            'jumlah' => $hasil->count(),
            'sebaran' => $sebaran,
        ];
    }

    /**
     * Pilihan semester untuk filter: dari periode yang sudah dibuat, terbaru lebih dulu.
     *
     * @return array<int,array{tahun:int,semester:int,label:string,nilai:string}>
     */
    public function daftarSemester(): array
    {
        $unik = [];
        foreach (PeriodePenilaian::orderByDesc('tahun')->orderByDesc('triwulan')->get(['tahun', 'triwulan']) as $p) {
            $semester = KpiCalculator::semesterDariTriwulan((int) $p->triwulan);
            $kunci = $p->tahun . '-' . $semester;
            $unik[$kunci] = [
                'tahun' => (int) $p->tahun,
                'semester' => $semester,
                'label' => "Semester {$semester} {$p->tahun}",
                'nilai' => $kunci,
            ];
        }

        return array_values($unik);
    }

    /** @param array<int,?float> $nilai */
    private function statistik(array $nilai): array
    {
        $ada = array_values(array_filter($nilai, fn ($n) => $n !== null));

        return [
            'rata' => $ada === [] ? null : round(array_sum($ada) / count($ada), 1),
            'tertinggi' => $ada === [] ? null : max($ada),
            'terendah' => $ada === [] ? null : min($ada),
            'jumlah' => count($ada),
        ];
    }

    /** @param array<int,string> $idTambahan karyawan yang punya data meski sudah nonaktif */
    private function karyawan(?string $idDept, array $idTambahan)
    {
        return Karyawan::with('departemen')
            ->when($idDept, fn ($q) => $q->where('id_dept', $idDept))
            ->where(function ($q) use ($idTambahan) {
                $q->where('status_karyawan', 'Aktif')->orWhereIn('id_karyawan', $idTambahan);
            })
            ->orderBy('nama_karyawan')
            ->get();
    }
}