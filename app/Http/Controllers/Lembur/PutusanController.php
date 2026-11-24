<?php

namespace App\Http\Controllers\Lembur;

use App\Http\Controllers\Concerns\DaftarLembur;
use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\RencanaLembur;
use App\Services\LemburService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Tahap 3 persetujuan: General Manager menyetujui atau menolak pengajuan yang sudah diketahui. */
class PutusanController extends Controller
{
    use DaftarLembur;

    public function index(Request $request)
    {
        $dasar = fn () => RencanaLembur::whereHas('persetujuan', fn ($q) => $q->where('tahap', 'ketahui'));

        $ringkasan = [
            'menunggu' => $dasar()->where('status_rencana', RencanaLembur::MENUNGGU_GM)->count(),
            'disetujui' => $dasar()->whereIn('status_rencana', [RencanaLembur::DISETUJUI, RencanaLembur::SELESAI])->count(),
            'ditolak' => $dasar()->where('status_rencana', RencanaLembur::DITOLAK)->count(),
            'jam' => $this->jamDisetujuiBulanIni(),
        ];

        $query = $this->filterLembur($dasar(), $request);
        match ($request->input('status')) {
            'menunggu' => $query->where('status_rencana', RencanaLembur::MENUNGGU_GM),
            'disetujui' => $query->whereIn('status_rencana', [RencanaLembur::DISETUJUI, RencanaLembur::SELESAI]),
            'ditolak' => $query->where('status_rencana', RencanaLembur::DITOLAK),
            default => null,
        };

        return view('lembur.putuskan.index', [
            'daftar' => $query->paginate(15)->withQueryString(),
            'ringkasan' => $ringkasan,
            'unit' => Departemen::orderBy('nama_dept')->get(),
        ]);
    }

    public function show(RencanaLembur $rencana)
    {
        $this->pastikanSudahDiketahui($rencana);

        return view('lembur.putuskan.show', ['rencana' => $this->muatDetailLembur($rencana)]);
    }

    public function setujui(Request $request, RencanaLembur $rencana, LemburService $layanan): RedirectResponse
    {
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:500']]);

        try {
            $layanan->setujui($rencana, $request->user(), $data['catatan'] ?? null);
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('lembur.putuskan.index')->with('sukses', "Pengajuan {$rencana->no_pengajuan} disetujui.");
    }

    public function formTolak(RencanaLembur $rencana)
    {
        $this->pastikanSudahDiketahui($rencana);
        if ($rencana->status_rencana !== RencanaLembur::MENUNGGU_GM) {
            return redirect()->route('lembur.putuskan.show', $rencana)->with('galat', 'Pengajuan ini sudah diputuskan.');
        }

        return view('lembur.putuskan.tolak', ['rencana' => $this->muatDetailLembur($rencana)]);
    }

    public function tolak(Request $request, RencanaLembur $rencana, LemburService $layanan): RedirectResponse
    {
        $data = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:500'],
            'saran' => ['nullable', 'string', 'max:500'],
        ], ['alasan_penolakan.required' => 'Alasan penolakan wajib diisi.']);

        try {
            $layanan->tolak($rencana, $request->user(), $data['alasan_penolakan'], $data['saran'] ?? null);
        } catch (DomainException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }

        return redirect()->route('lembur.putuskan.index')->with('sukses', "Pengajuan {$rencana->no_pengajuan} ditolak.");
    }

    private function pastikanSudahDiketahui(RencanaLembur $rencana): void
    {
        abort_unless($rencana->persetujuan()->where('tahap', 'ketahui')->exists(), 404);
    }
}
