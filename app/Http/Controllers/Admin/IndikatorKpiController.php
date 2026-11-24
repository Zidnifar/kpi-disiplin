<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Attendance\AttendanceRules;
use App\Http\Controllers\Controller;
use App\Models\DetailPenilaianKpi;
use App\Models\IndikatorKpi;
use App\Models\PelanggaranKaryawan;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Kriteria disiplin (indikator KPI): kode, nama, dan bobot poin pengurang per pelanggaran. */
class IndikatorKpiController extends Controller
{
    public function index(Request $request)
    {
        $kata = trim((string) $request->input('q'));

        $daftar = IndikatorKpi::query()
            ->when($kata !== '', function ($q) use ($kata) {
                $like = Format::like($kata);
                $q->where(fn ($w) => $w->where('kode_indikator', 'like', $like)->orWhere('nama_indikator', 'like', $like));
            })
            ->orderBy('kode_indikator')
            ->paginate(8)
            ->withQueryString();

        $ringkasan = [
            'total' => IndikatorKpi::count(),
            'aktif' => IndikatorKpi::where('status_indikator', 'Aktif')->count(),
            'nonaktif' => IndikatorKpi::where('status_indikator', '!=', 'Aktif')->count(),
        ];

        return view('admin.kpi.indikator.index', [
            'daftar' => $daftar,
            'ringkasan' => $ringkasan,
            'kodeSistem' => $this->kodeSistem(),
            'dipakai' => $this->idDipakai(),
        ]);
    }

    public function create()
    {
        return view('admin.kpi.indikator.form', ['indikator' => null, 'sistem' => false]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request, null);
        IndikatorKpi::create($data);

        return redirect()->route('admin.kpi.indikator.index')->with('sukses', "Kriteria {$data['kode_indikator']} berhasil ditambahkan.");
    }

    public function edit(IndikatorKpi $indikator)
    {
        return view('admin.kpi.indikator.form', [
            'indikator' => $indikator,
            'sistem' => $this->bawaanSistem($indikator),
        ]);
    }

    public function update(Request $request, IndikatorKpi $indikator): RedirectResponse
    {
        // kriteria yang dipakai aturan absensi: kode dan status dikunci, hanya nama dan bobot yang boleh diubah
        if ($this->bawaanSistem($indikator)) {
            $request->merge(['kode_indikator' => $indikator->kode_indikator, 'status_indikator' => 'Aktif']);
        }

        $data = $this->validasi($request, $indikator);
        $indikator->update($data);

        return redirect()->route('admin.kpi.indikator.index')->with('sukses', "Kriteria {$indikator->kode_indikator} berhasil diperbarui.");
    }

    public function destroy(IndikatorKpi $indikator): RedirectResponse
    {
        if ($this->bawaanSistem($indikator)) {
            return redirect()->route('admin.kpi.indikator.index')
                ->with('galat', "Kriteria {$indikator->kode_indikator} dipakai langsung oleh aturan absensi sehingga tidak dapat dihapus.");
        }
        if ($this->idDipakai()->contains($indikator->id_indikator)) {
            return redirect()->route('admin.kpi.indikator.index')
                ->with('galat', "Kriteria {$indikator->kode_indikator} sudah dipakai pada data pelanggaran atau hasil KPI sehingga tidak dapat dihapus. Nonaktifkan saja bila tidak dipakai lagi.");
        }

        $kode = $indikator->kode_indikator;
        $indikator->delete();

        return redirect()->route('admin.kpi.indikator.index')->with('sukses', "Kriteria {$kode} berhasil dihapus.");
    }

    /** @return array<string,mixed> */
    private function validasi(Request $request, ?IndikatorKpi $indikator): array
    {
        $request->merge(['kode_indikator' => strtoupper(trim((string) $request->input('kode_indikator')))]);

        $unik = Rule::unique('indikator_kpi', 'kode_indikator');
        if ($indikator) {
            $unik->ignore($indikator->id_indikator, 'id_indikator');
        }

        return $request->validate([
            'kode_indikator' => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/', $unik],
            'nama_indikator' => ['required', 'string', 'max:100'],
            'bobot_poin' => ['required', 'integer', 'between:1,100'],
            'status_indikator' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
        ], [
            'kode_indikator.regex' => 'Kode hanya boleh berisi huruf dan angka tanpa spasi.',
            'kode_indikator.unique' => 'Kode sudah dipakai kriteria lain.',
            'bobot_poin.between' => 'Bobot harus antara 1 sampai 100 poin.',
        ], [
            'kode_indikator' => 'Kode',
            'nama_indikator' => 'Kriteria',
            'bobot_poin' => 'Bobot',
            'status_indikator' => 'Status',
        ]);
    }

    /** Kode yang dihasilkan aturan absensi (mis. TMK, A, ITMTP, STSK). Menghapus atau mengganti kodenya merusak perhitungan. */
    private function kodeSistem(): array
    {
        $kode = [];
        foreach (AttendanceRules::statusPilihan() as $status) {
            $kode = array_merge($kode, array_keys(AttendanceRules::pelanggaranDariStatus($status)));
        }

        return array_values(array_unique($kode));
    }

    private function bawaanSistem(IndikatorKpi $indikator): bool
    {
        return in_array($indikator->kode_indikator, $this->kodeSistem(), true);
    }

    /** Id kriteria yang sudah dipakai pada catatan pelanggaran atau rincian hasil KPI. */
    private function idDipakai()
    {
        return PelanggaranKaryawan::query()->distinct()->pluck('id_indikator')
            ->merge(DetailPenilaianKpi::query()->distinct()->pluck('id_indikator'))
            ->unique()
            ->values();
    }
}