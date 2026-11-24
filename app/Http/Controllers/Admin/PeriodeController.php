<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HasilKpi;
use App\Models\PeriodePenilaian;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Support\Format;

class PeriodeController extends Controller
{
    public function index(Request $request)
    {
        $hariIni = today()->toDateString();
        $kata = trim((string) $request->input('q'));

        $daftar = PeriodePenilaian::query()
            ->when($kata !== '', function ($q) use ($kata) {
                $like = Format::like($kata);
                $q->where(fn ($w) => $w->where('nama_periode', 'like', $like)->orWhere('tahun', 'like', $like));
            })
            ->orderByDesc('tahun')->orderByDesc('triwulan')
            ->paginate(15)
            ->withQueryString();

        $ringkasan = [
            'total' => PeriodePenilaian::count(),
            'berlangsung' => PeriodePenilaian::whereDate('tanggal_mulai', '<=', $hariIni)->whereDate('tanggal_selesai', '>=', $hariIni)->count(),
            'berakhir' => PeriodePenilaian::whereDate('tanggal_selesai', '<', $hariIni)->count(),
        ];

        return view('admin.kpi.periode.index', compact('daftar', 'ringkasan'));
    }

    public function create()
    {
        return view('admin.kpi.periode.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->aturan(), $this->pesan());

        $tahun = (int) $data['tahun'];
        $tw = (int) $data['triwulan'];

        if (PeriodePenilaian::where('tahun', $tahun)->where('triwulan', $tw)->exists()) {
            return back()->withInput()->withErrors(['triwulan' => "Periode Triwulan {$tw} {$tahun} sudah ada."]);
        }

        [$mulai, $selesai] = $this->tanggalPeriode($data, $tahun, $tw);

        PeriodePenilaian::create([
            'nama_periode' => "Triwulan {$tw} {$tahun}",
            'tahun' => $tahun,
            'triwulan' => $tw,
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'status_periode' => 'Berjalan',
        ]);

        return redirect()->route('admin.kpi.periode.index')->with('sukses', "Periode Triwulan {$tw} {$tahun} berhasil dibuat.");
    }

    /** Form ubah periode (memakai view yang sama dengan form tambah). */
    public function edit(PeriodePenilaian $periode)
    {
        if ($this->terkunci($periode)) {
            return redirect()->route('admin.kpi.periode.index')
                ->with('galat', 'Periode ini sudah punya hasil tervalidasi dan tidak bisa diubah.');
        }

        return view('admin.kpi.periode.form', ['periode' => $periode]);
    }

    /** Simpan perubahan periode. */
    public function update(Request $request, PeriodePenilaian $periode): RedirectResponse
    {
        if ($this->terkunci($periode)) {
            return redirect()->route('admin.kpi.periode.index')
                ->with('galat', 'Periode ini sudah punya hasil tervalidasi dan tidak bisa diubah.');
        }

        $data = $request->validate($this->aturan(), $this->pesan());

        $tahun = (int) $data['tahun'];
        $tw = (int) $data['triwulan'];

        $sudahAda = PeriodePenilaian::where('tahun', $tahun)
            ->where('triwulan', $tw)
            ->where('id_periode', '!=', $periode->id_periode)
            ->exists();
        if ($sudahAda) {
            return back()->withInput()->withErrors(['triwulan' => "Periode Triwulan {$tw} {$tahun} sudah ada."]);
        }

        [$mulai, $selesai] = $this->tanggalPeriode($data, $tahun, $tw);

        $periode->update([
            'nama_periode' => "Triwulan {$tw} {$tahun}",
            'tahun' => $tahun,
            'triwulan' => $tw,
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
        ]);

        return redirect()->route('admin.kpi.periode.index')->with('sukses', "Periode Triwulan {$tw} {$tahun} berhasil diperbarui.");
    }

    /** Hapus periode yang belum punya hasil KPI. */
    public function destroy(PeriodePenilaian $periode): RedirectResponse
    {
        if (HasilKpi::where('id_periode', $periode->id_periode)->exists()) {
            return redirect()->route('admin.kpi.periode.index')
                ->with('galat', 'Periode tidak bisa dihapus karena sudah punya hasil KPI.');
        }

        try {
            $periode->delete();
        } catch (QueryException $e) {
            report($e); // detailnya ada di storage/logs/laravel.log

            return redirect()->route('admin.kpi.periode.index')
                ->with('galat', 'Periode gagal dihapus karena masih terikat dengan data lain.');
        }

        return redirect()->route('admin.kpi.periode.index')->with('sukses', 'Periode berhasil dihapus.');
    }

    /** Periode yang sudah punya hasil tervalidasi dikunci agar nilai yang sudah sah tidak berubah. */
    private function terkunci(PeriodePenilaian $periode): bool
    {
        return HasilKpi::where('id_periode', $periode->id_periode)
            ->where('status_validasi', 'Tervalidasi')
            ->exists();
    }

    /** @return array<string,mixed> */
    private function aturan(): array
    {
        return [
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'triwulan' => ['required', 'integer', 'between:1,4'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ];
    }

    /** @return array<string,string> */
    private function pesan(): array
    {
        return [
            'tahun.between' => 'Tahun harus antara 2000 dan 2100.',
            'triwulan.between' => 'Pilih triwulan 1 sampai 4.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ];
    }

    /**
     * Tanggal mulai/selesai periode; kalau dikosongkan, diisi otomatis sesuai triwulan.
     *
     * @return array{0:Carbon,1:Carbon}
     */
    private function tanggalPeriode(array $data, int $tahun, int $tw): array
    {
        $awal = Carbon::create($tahun, ($tw - 1) * 3 + 1, 1)->startOfDay();
        $mulai = ! empty($data['tanggal_mulai']) ? Carbon::parse($data['tanggal_mulai']) : $awal;
        $selesai = ! empty($data['tanggal_selesai']) ? Carbon::parse($data['tanggal_selesai']) : $awal->copy()->addMonths(2)->endOfMonth();

        return [$mulai, $selesai];
    }
}