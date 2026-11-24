<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HasilKpi;
use App\Models\PeriodePenilaian;
use App\Services\KpiService;
use App\Support\Format;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HasilKpiController extends Controller
{
    public function index(Request $request, KpiService $kpi)
    {
        $periodeList = PeriodePenilaian::orderByDesc('tahun')->orderByDesc('triwulan')->get();

        // Pilih periode: dari dropdown Triwulan + Tahun, atau ?periode=id (dipakai redirect), atau yang terbaru.
        $periode = null;
        if ($request->filled('triwulan') && $request->filled('tahun')) {
            $periode = $periodeList->first(fn ($p) => (int) $p->triwulan === (int) $request->input('triwulan')
                && (int) $p->tahun === (int) $request->input('tahun'));
        }
        $periode = $periode
            ?? $periodeList->firstWhere('id_periode', $request->input('periode'))
            ?? PeriodePenilaian::whereHas('hasilKpi')->orderByDesc('tahun')->orderByDesc('triwulan')->first()
            ?? $periodeList->first();

        if (! $periode) {
            return view('admin.kpi.hasil.index', ['periode' => null, 'periodeList' => $periodeList]);
        }

        $dasar = fn () => HasilKpi::where('id_periode', $periode->id_periode);
        $ringkasan = [
            'dinilai' => $dasar()->count(),
            'valid' => $dasar()->where('status_validasi', KpiService::VALIDASI_OK)->count(),
            'rata' => $dasar()->avg('nilai_triwulan'),
            'tertinggi' => $dasar()->max('nilai_triwulan'),
        ];

        // Semua hasil periode ini (tanpa paginasi) untuk kartu distribusi & rata-rata indikator.
        $semuaHasil = $dasar()->with(['predikat', 'detail.indikator'])->get();

        // Jumlah karyawan per kategori
        $distribusi = ['Sangat Baik' => 0, 'Baik' => 0, 'Cukup' => 0, 'Kurang' => 0];
        foreach ($semuaHasil as $h) {
            $nama = $h->predikat->nama_predikat ?? null;
            if ($nama !== null && array_key_exists($nama, $distribusi)) {
                $distribusi[$nama]++;
            }
        }

        // Rata-rata nilai per indikator
        // ASUMSI: detail punya kolom `nilai`, indikator punya kolom `nama_indikator`. Sesuaikan jika berbeda.
        $indikator = $semuaHasil
            ->flatMap(fn ($h) => $h->detail)
            ->groupBy(fn ($d) => $d->indikator->nama_indikator ?? 'Lainnya')
            ->map(fn ($kelompok, $label) => ['label' => $label, 'nilai' => round((float) $kelompok->avg('nilai'), 1)])
            ->values()
            ->all();

        $daftar = $dasar()
            ->with(['karyawan.departemen', 'karyawan.pengguna', 'predikat', 'periode', 'detail.indikator', 'pemeriksa.karyawan', 'pemvalidasi'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $kata = Format::like((string) $request->input('q'));
                $q->whereHas('karyawan', fn ($k) => $k->where('nama_karyawan', 'like', $kata)->orWhere('nip', 'like', $kata));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status_validasi', $request->input('status')))
            ->orderByDesc('nilai_triwulan')
            ->paginate(20)
            ->withQueryString();

        // apakah tiap hasil wajib diperiksa kepala unit (dipakai pop up rincian)
        $perluDiperiksa = $daftar->getCollection()
            ->mapWithKeys(fn ($h) => [$h->id_hasil => $kpi->perluDiperiksa($h)])
            ->all();

        $triwulanList = [1, 2, 3, 4];
        $tahunList = $periodeList->pluck('tahun')->unique()->sortDesc()->values()->all();
        $triwulanAktif = (int) $periode->triwulan;
        $tahunAktif = (int) $periode->tahun;

        return view('admin.kpi.hasil.index', compact(
            'periode', 'periodeList', 'ringkasan', 'daftar',
            'distribusi', 'indikator', 'triwulanList', 'tahunList', 'triwulanAktif', 'tahunAktif',
            'perluDiperiksa'
        ));
    }

    public function show(HasilKpi $hasil, KpiService $kpi)
    {
        $hasil->load(['karyawan.departemen', 'karyawan.pengguna', 'periode', 'predikat', 'detail.indikator', 'pemeriksa.karyawan', 'pemvalidasi']);

        return view('admin.kpi.hasil.show', [
            'hasil' => $hasil,
            'perluDiperiksa' => $kpi->perluDiperiksa($hasil),
        ]);
    }

    public function validasi(Request $request, HasilKpi $hasil, KpiService $kpi): RedirectResponse
    {
        $data = $request->validate(['catatan_validasi' => ['nullable', 'string', 'max:500']]);

        if ($hasil->status_validasi === KpiService::VALIDASI_OK) {
            return back()->with('galat', 'Hasil ini sudah divalidasi.');
        }

        try {
            $kpi->validasi(
                $hasil->id_hasil,
                $request->user()->id_pengguna,
                $data['catatan_validasi'] ?? null,
                $kpi->perluDiperiksa($hasil)
            );
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('admin.kpi.hasil.index', ['periode' => $hasil->id_periode])
            ->with('sukses', 'Hasil KPI berhasil divalidasi.');
    }

    public function kembalikan(Request $request, HasilKpi $hasil, KpiService $kpi): RedirectResponse
    {
        $data = $request->validate(['catatan_validasi' => ['nullable', 'string', 'max:500']]);

        if ($hasil->status_validasi === KpiService::VALIDASI_OK) {
            return back()->with('galat', 'Hasil yang sudah tervalidasi tidak dapat dikembalikan dari sini.');
        }

        $kpi->kembalikan($hasil->id_hasil, $request->user()->id_pengguna, $data['catatan_validasi'] ?? 'Dikembalikan untuk dihitung ulang.');

        return redirect()->route('admin.kpi.hasil.index', ['periode' => $hasil->id_periode])
            ->with('sukses', 'Hasil dikembalikan. Hitung ulang periode ini setelah data diperbaiki.');
    }

    /** Validasi semua hasil yang sudah siap: sudah diperiksa kepala unit, atau memang tidak memerlukan pemeriksaan. */
    public function validasiSemua(Request $request, PeriodePenilaian $periode, KpiService $kpi): RedirectResponse
    {
        $berhasil = 0;
        $dilewati = 0;

        $daftar = HasilKpi::with('karyawan.pengguna')
            ->where('id_periode', $periode->id_periode)
            ->where('status_validasi', '!=', KpiService::VALIDASI_OK)
            ->get();

        foreach ($daftar as $hasil) {
            $perlu = $kpi->perluDiperiksa($hasil);
            if ($perlu && $hasil->status_periksa !== KpiService::PERIKSA_SUDAH) {
                $dilewati++;
                continue;
            }
            $kpi->validasi($hasil->id_hasil, $request->user()->id_pengguna, null, $perlu);
            $berhasil++;
        }

        $pesan = "{$berhasil} hasil KPI divalidasi.";
        if ($dilewati > 0) {
            return redirect()->route('admin.kpi.hasil.index', ['periode' => $periode->id_periode])
                ->with('sukses', $pesan)
                ->with('peringatan', "{$dilewati} hasil dilewati karena belum diperiksa kepala unit.");
        }

        return redirect()->route('admin.kpi.hasil.index', ['periode' => $periode->id_periode])->with('sukses', $pesan);
    }
}