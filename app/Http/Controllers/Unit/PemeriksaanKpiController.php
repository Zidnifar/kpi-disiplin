<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Concerns\LingkupKaryawan;
use App\Http\Controllers\Controller;
use App\Models\HasilKpi;
use App\Models\Karyawan;
use App\Models\PeriodePenilaian;
use App\Services\KpiService;
use App\Support\Format;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Kepala unit memeriksa hasil KPI karyawan di departemennya (tidak termasuk dirinya sendiri). */
class PemeriksaanKpiController extends Controller
{
    use LingkupKaryawan;

    public function index(Request $request)
    {
        $saya = $this->karyawanSaya();

        $periodeList = PeriodePenilaian::orderByDesc('tahun')->orderByDesc('triwulan')->get();
        $periode = $periodeList->firstWhere('id_periode', $request->input('periode'))
            ?? PeriodePenilaian::whereHas('hasilKpi')->orderByDesc('tahun')->orderByDesc('triwulan')->first()
            ?? $periodeList->first();

        if (! $periode) {
            return view('unit.kpi.pemeriksaan.index', ['periode' => null, 'periodeList' => $periodeList]);
        }

        $dasar = fn () => HasilKpi::where('id_periode', $periode->id_periode)
            ->whereHas('karyawan', fn ($q) => $q->where('id_dept', $saya->id_dept)->where('id_karyawan', '!=', $saya->id_karyawan));

        $ringkasan = [
            'karyawan' => Karyawan::where('id_dept', $saya->id_dept)->where('status_karyawan', 'Aktif')->where('id_karyawan', '!=', $saya->id_karyawan)->count(),
            'dihitung' => $dasar()->count(),
            'belum' => $dasar()->where('status_periksa', KpiService::PERIKSA_BELUM)->count(),
            'sudah' => $dasar()->where('status_periksa', KpiService::PERIKSA_SUDAH)->count(),
        ];

        $daftar = $dasar()
            ->with(['karyawan', 'predikat'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $kata = Format::like((string) $request->input('q'));
                $q->whereHas('karyawan', fn ($k) => $k->where('nama_karyawan', 'like', $kata)->orWhere('nip', 'like', $kata));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status_periksa', $request->input('status')))
            ->orderByRaw("case when status_periksa = 'Belum diperiksa' then 0 else 1 end")
            ->orderByDesc('nilai_triwulan')
            ->paginate(20)
            ->withQueryString();

        return view('unit.kpi.pemeriksaan.index', compact('periode', 'periodeList', 'ringkasan', 'daftar'));
    }

    public function show(HasilKpi $hasil)
    {
        $this->pastikanBolehMemeriksa($hasil);
        $hasil->load(['karyawan.departemen', 'periode', 'predikat', 'detail.indikator']);

        return view('unit.kpi.pemeriksaan.show', ['hasil' => $hasil]);
    }

    public function tandai(Request $request, HasilKpi $hasil, KpiService $kpi): RedirectResponse
    {
        $this->pastikanBolehMemeriksa($hasil);
        $data = $request->validate(['catatan_periksa' => ['nullable', 'string', 'max:500']]);

        try {
            $kpi->periksa($hasil->id_hasil, $request->user()->id_pengguna, $data['catatan_periksa'] ?? null);
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('unit.kpi.pemeriksaan', ['periode' => $hasil->id_periode])
            ->with('sukses', 'Hasil KPI ditandai sudah diperiksa.');
    }

    private function pastikanBolehMemeriksa(HasilKpi $hasil): void
    {
        $saya = $this->karyawanSaya();
        $milik = Karyawan::where('id_karyawan', $hasil->id_karyawan)->where('id_dept', $saya->id_dept)->exists();
        abort_unless($milik && $hasil->id_karyawan !== $saya->id_karyawan, 404);
    }
}
