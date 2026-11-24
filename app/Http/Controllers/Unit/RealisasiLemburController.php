<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Concerns\DaftarLembur;
use App\Http\Controllers\Concerns\LingkupKaryawan;
use App\Http\Controllers\Controller;
use App\Models\RencanaLembur;
use App\Services\LemburService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Kepala unit mengisi jam lembur yang benar-benar dilaksanakan untuk rencana yang sudah disetujui. */
class RealisasiLemburController extends Controller
{
    use DaftarLembur, LingkupKaryawan;

    public function index(Request $request)
    {
        $saya = $this->karyawanSaya();

        $semua = RencanaLembur::with('detail')
            ->where('id_dept', $saya->id_dept)
            ->whereIn('status_rencana', [RencanaLembur::DISETUJUI, RencanaLembur::SELESAI])
            ->when($request->filled('status'), fn ($q) => $q->where('status_rencana', $request->input('status')))
            ->orderByDesc('tanggal_lembur')
            ->get();

        $selesai = $semua->where('status_rencana', RencanaLembur::SELESAI);
        $ringkasan = [
            'belum' => $semua->where('status_rencana', RencanaLembur::DISETUJUI)->count(),
            'sudah' => $selesai->count(),
            'realisasi' => $selesai->sum(fn (RencanaLembur $r) => $r->totalJamRealisasi() ?? 0),
            'selisih' => $selesai->sum(fn (RencanaLembur $r) => ($r->totalJamRealisasi() ?? 0) - $r->totalJamRencana()),
        ];

        return view('unit.realisasi.index', ['daftar' => $semua, 'ringkasan' => $ringkasan]);
    }

    public function edit(RencanaLembur $rencana)
    {
        $this->pastikanMilikUnit($rencana);
        abort_unless($rencana->disetujui(), 404);

        return view('unit.realisasi.form', ['rencana' => $this->muatDetailLembur($rencana)]);
    }

    public function update(Request $request, RencanaLembur $rencana, LemburService $layanan): RedirectResponse
    {
        $this->pastikanMilikUnit($rencana);

        $data = $request->validate([
            'baris' => ['required', 'array'],
            'baris.*.jam_mulai_real' => ['required', 'date_format:H:i'],
            'baris.*.jam_selesai_real' => ['required', 'date_format:H:i'],
            'baris.*.catatan_realisasi' => ['nullable', 'string', 'max:255'],
        ], [
            'baris.*.jam_mulai_real.required' => 'Isi jam mulai untuk semua karyawan.',
            'baris.*.jam_selesai_real.required' => 'Isi jam selesai untuk semua karyawan.',
            'baris.*.jam_mulai_real.date_format' => 'Format jam mulai tidak valid.',
            'baris.*.jam_selesai_real.date_format' => 'Format jam selesai tidak valid.',
        ]);

        try {
            $layanan->isiRealisasi($rencana, $this->karyawanSaya(), $data['baris']);
        } catch (DomainException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }

        return redirect()->route('unit.realisasi.index')->with('sukses', "Realisasi {$rencana->no_pengajuan} disimpan.");
    }

    private function pastikanMilikUnit(RencanaLembur $rencana): void
    {
        abort_unless($rencana->id_dept === $this->karyawanSaya()->id_dept, 404);
    }
}
