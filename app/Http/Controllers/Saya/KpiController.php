<?php

namespace App\Http\Controllers\Saya;

use App\Http\Controllers\Concerns\LingkupKaryawan;
use App\Http\Controllers\Controller;
use App\Models\HasilKpi;
use App\Models\NilaiSemester;
use App\Models\PeriodePenilaian;
use App\Models\PredikatNilai;
use App\Services\KpiService;
use Illuminate\Http\Request;

/** Halaman KPI pribadi. Hanya hasil yang sudah divalidasi admin yang ditampilkan ke karyawan. */
class KpiController extends Controller
{
    use LingkupKaryawan;

    public function nilai(Request $request)
    {
        $saya = $this->karyawanSaya();
        $tahunPilihan = $this->tahunPilihan($saya->id_karyawan);
        $tahun = (int) $request->input('tahun', $tahunPilihan[0] ?? now()->year);

        $hasil = $this->hasilValid($saya->id_karyawan)
            ->filter(fn (HasilKpi $h) => (int) $h->periode->tahun === $tahun);

        $tw = [];
        for ($i = 1; $i <= 4; $i++) {
            $tw[$i] = $hasil->first(fn (HasilKpi $h) => (int) $h->periode->triwulan === $i);
        }
        $terakhir = $hasil->sortByDesc(fn (HasilKpi $h) => $h->periode->triwulan)->first();

        $triwulanPilihan = [1, 2, 3, 4];
        $defaultTriwulan = (int) ($terakhir?->periode?->triwulan ?? 1);
        $triwulan = (int) $request->input('triwulan', $defaultTriwulan);
        if (! in_array($triwulan, $triwulanPilihan, true)) {
            $triwulan = $defaultTriwulan;
        }

        $hasilRincian = $tw[$triwulan] ?? null;
        $hasilRincian?->load(['detail.indikator', 'predikat']);

        $semester = NilaiSemester::with('predikat')
            ->where('id_karyawan', $saya->id_karyawan)->where('tahun', $tahun)->get()->keyBy('semester');

        return view('saya.kpi.nilai', [
            'tahun' => $tahun,
            'tahunPilihan' => $tahunPilihan,
            'triwulan' => $triwulan,
            'triwulanPilihan' => $triwulanPilihan,
            'tw' => $tw,
            'terakhir' => $terakhir,
            'hasilRincian' => $hasilRincian,
            'semester' => $semester,
            'skala' => PredikatNilai::where('status_predikat', 'Aktif')->orderByDesc('nilai_min')->get(),
        ]);
    }

    public function rincian(Request $request)
    {
        $saya = $this->karyawanSaya();
        $semua = $this->hasilValid($saya->id_karyawan)
            ->sortByDesc(fn (HasilKpi $h) => $h->periode->tahun * 10 + $h->periode->triwulan)
            ->values();

        $hasil = $semua->firstWhere('id_periode', $request->input('periode')) ?? $semua->first();
        $hasil?->load(['detail.indikator', 'predikat']);

        return view('saya.kpi.rincian', ['semua' => $semua, 'hasil' => $hasil]);
    }

    public function riwayat()
    {
        $saya = $this->karyawanSaya();
        $semua = $this->hasilValid($saya->id_karyawan)
            ->sortBy(fn (HasilKpi $h) => $h->periode->tahun * 10 + $h->periode->triwulan)
            ->values();

        $semester = NilaiSemester::with(['predikat', 'hasilTwPertama.periode', 'hasilTwKedua.periode'])
            ->where('id_karyawan', $saya->id_karyawan)
            ->orderByDesc('tahun')->orderByDesc('semester')
            ->get();

        return view('saya.kpi.riwayat', ['semua' => $semua, 'semester' => $semester]);
    }

    private function hasilValid(string $idKaryawan)
    {
        return HasilKpi::with(['periode', 'predikat'])
            ->where('id_karyawan', $idKaryawan)
            ->where('status_validasi', KpiService::VALIDASI_OK)
            ->get();
    }

    /** @return array<int,int> */
    private function tahunPilihan(string $idKaryawan): array
    {
        $tahun = $this->hasilValid($idKaryawan)->map(fn (HasilKpi $h) => (int) $h->periode->tahun)->unique()->sortDesc()->values()->all();

        return $tahun ?: [(int) now()->year];
    }
}