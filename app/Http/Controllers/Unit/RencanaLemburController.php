<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Concerns\DaftarLembur;
use App\Http\Controllers\Concerns\LingkupKaryawan;
use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\RencanaLembur;
use App\Models\ShiftKerja;
use App\Services\LemburService;
use App\Support\Format;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/** Kepala unit membuat, mengubah, dan mengajukan rencana lembur untuk karyawan di departemennya. */
class RencanaLemburController extends Controller
{
    use DaftarLembur, LingkupKaryawan;

    public function index(Request $request)
    {
        $saya = $this->karyawanSaya();
        $dasar = fn () => RencanaLembur::where('id_dept', $saya->id_dept);

        $ringkasan = [
            'total' => $dasar()->count(),
            'proses' => $dasar()->whereIn('status_rencana', [RencanaLembur::MENUNGGU_PRODUKSI, RencanaLembur::MENUNGGU_OPERASIONAL, RencanaLembur::MENUNGGU_GM])->count(),
            'disetujui' => $dasar()->whereIn('status_rencana', [RencanaLembur::DISETUJUI, RencanaLembur::SELESAI])->count(),
            'ditolak' => $dasar()->where('status_rencana', RencanaLembur::DITOLAK)->count(),
        ];

        $daftar = $dasar()
            ->with('detail')
            ->when($request->filled('q'), fn ($q) => $q->where('no_pengajuan', 'like', Format::like((string) $request->input('q'))))
            ->when($request->filled('status'), fn ($q) => $q->where('status_rencana', $request->input('status')))
            ->orderByDesc('tanggal_lembur')
            ->orderByDesc('no_pengajuan')
            ->paginate(15)
            ->withQueryString();

        return view('unit.lembur.index', compact('daftar', 'ringkasan'));
    }

    public function create()
    {
        return view('unit.lembur.form', $this->dataForm(null));
    }

    public function store(Request $request, LemburService $layanan): RedirectResponse
    {
        $saya = $this->karyawanSaya();
        $data = $request->validate($this->aturan($saya), $this->pesan());
        $ajukan = $request->input('aksi') === 'ajukan';

        try {
            $rencana = $layanan->simpan($saya, $this->isian($data), array_values($data['karyawan']), null, $ajukan);
        } catch (DomainException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }

        return redirect()->route('unit.lembur.show', $rencana)->with(
            'sukses',
            $ajukan ? "Pengajuan {$rencana->no_pengajuan} dikirim ke Kepala Produksi." : "Draf {$rencana->no_pengajuan} disimpan."
        );
    }

    public function show(RencanaLembur $rencana)
    {
        $this->pastikanMilikUnit($rencana);

        return view('unit.lembur.show', ['rencana' => $this->muatDetailLembur($rencana)]);
    }

    public function edit(RencanaLembur $rencana)
    {
        $this->pastikanMilikUnit($rencana);
        if (! $rencana->bolehDiubah()) {
            return redirect()->route('unit.lembur.show', $rencana)->with('galat', 'Pengajuan yang sedang diproses tidak dapat diubah.');
        }

        return view('unit.lembur.form', $this->dataForm($rencana->load('detail')));
    }

    public function update(Request $request, RencanaLembur $rencana, LemburService $layanan): RedirectResponse
    {
        $this->pastikanMilikUnit($rencana);
        $saya = $this->karyawanSaya();
        $data = $request->validate($this->aturan($saya), $this->pesan());
        $ajukan = $request->input('aksi') === 'ajukan';

        try {
            $layanan->simpan($saya, $this->isian($data), array_values($data['karyawan']), $rencana, $ajukan);
        } catch (DomainException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }

        return redirect()->route('unit.lembur.show', $rencana)->with(
            'sukses',
            $ajukan ? 'Pengajuan dikirim ke Kepala Produksi.' : 'Perubahan draf disimpan.'
        );
    }

    public function ajukan(RencanaLembur $rencana, LemburService $layanan): RedirectResponse
    {
        $this->pastikanMilikUnit($rencana);

        try {
            $layanan->ajukan($rencana);
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('unit.lembur.show', $rencana)->with('sukses', 'Pengajuan dikirim ke Kepala Produksi.');
    }

    private function pastikanMilikUnit(RencanaLembur $rencana): void
    {
        abort_unless($rencana->id_dept === $this->karyawanSaya()->id_dept, 404);
    }

    /** @return array<string,mixed> */
    private function dataForm(?RencanaLembur $rencana): array
    {
        $saya = $this->karyawanSaya();

        return [
            'rencana' => $rencana,
            'shiftPilihan' => ShiftKerja::where('status_shift', 'Aktif')->orderBy('jam_mulai')->get(),
            'karyawanPilihan' => Karyawan::where('id_dept', $saya->id_dept)->where('status_karyawan', 'Aktif')->orderBy('nama_karyawan')->get(),
            'nomor' => $rencana?->no_pengajuan,
            'departemen' => $saya->departemen,
        ];
    }

    /** @return array<string,mixed> */
    private function aturan(Karyawan $saya): array
    {
        return [
            'tanggal_lembur' => ['required', 'date', 'after_or_equal:today'],
            'id_shift' => ['nullable', Rule::exists('shift_kerja', 'id_shift')],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'different:jam_mulai'],
            'uraian_pekerjaan' => ['required', 'string', 'max:1000'],
            'alasan_lembur' => ['required', 'string', 'max:1000'],
            'karyawan' => ['required', 'array', 'min:1'],
            'karyawan.*.id_karyawan' => [
                'required', 'distinct',
                Rule::exists('karyawan', 'id_karyawan')->where(fn ($q) => $q->where('id_dept', $saya->id_dept)->where('status_karyawan', 'Aktif')),
            ],
            'karyawan.*.pekerjaan' => ['nullable', 'string', 'max:100'],
        ];
    }

    /** @return array<string,string> */
    private function pesan(): array
    {
        return [
            'tanggal_lembur.after_or_equal' => 'Tanggal lembur tidak boleh sebelum hari ini.',
            'jam_selesai.different' => 'Jam selesai harus berbeda dari jam mulai.',
            'karyawan.required' => 'Tambahkan minimal satu karyawan yang lembur.',
            'karyawan.min' => 'Tambahkan minimal satu karyawan yang lembur.',
            'karyawan.*.id_karyawan.required' => 'Pilih karyawan pada setiap baris.',
            'karyawan.*.id_karyawan.distinct' => 'Karyawan yang sama dipilih lebih dari sekali.',
            'karyawan.*.id_karyawan.exists' => 'Karyawan harus berasal dari departemen Anda.',
        ];
    }

    /** @return array<string,mixed> */
    private function isian(array $data): array
    {
        return Arr::only($data, ['tanggal_lembur', 'id_shift', 'jam_mulai', 'jam_selesai', 'uraian_pekerjaan', 'alasan_lembur']);
    }
}
