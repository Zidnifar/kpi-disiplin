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

/** Tahap 1 persetujuan: Kepala Produksi memeriksa pengajuan dari kepala unit. */
class PemeriksaanController extends Controller
{
    use DaftarLembur;

    public function index(Request $request)
    {
        $dasar = fn () => RencanaLembur::where('status_rencana', '!=', RencanaLembur::DRAF);

        $ringkasan = [
            'menunggu' => $dasar()->where('status_rencana', RencanaLembur::MENUNGGU_PRODUKSI)->count(),
            'diperiksa' => $dasar()->whereHas('persetujuan', fn ($q) => $q->where('tahap', 'periksa')->where('status_persetujuan', 'Diperiksa'))->count(),
            'dikembalikan' => $dasar()->where('status_rencana', RencanaLembur::DIKEMBALIKAN)->count(),
            'jam' => $this->jamDisetujuiBulanIni(),
        ];

        $query = $this->filterLembur($dasar(), $request);
        match ($request->input('status')) {
            'menunggu' => $query->where('status_rencana', RencanaLembur::MENUNGGU_PRODUKSI),
            'diperiksa' => $query->whereHas('persetujuan', fn ($q) => $q->where('tahap', 'periksa')->where('status_persetujuan', 'Diperiksa')),
            'dikembalikan' => $query->where('status_rencana', RencanaLembur::DIKEMBALIKAN),
            default => null,
        };

        return view('lembur.periksa.index', [
            'daftar' => $query->paginate(15)->withQueryString(),
            'ringkasan' => $ringkasan,
            'unit' => Departemen::orderBy('nama_dept')->get(),
        ]);
    }

    public function show(RencanaLembur $rencana)
    {
        abort_if($rencana->status_rencana === RencanaLembur::DRAF, 404);

        return view('lembur.periksa.show', ['rencana' => $this->muatDetailLembur($rencana)]);
    }

    public function periksa(Request $request, RencanaLembur $rencana, LemburService $layanan): RedirectResponse
    {
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:500']]);

        try {
            $layanan->periksa($rencana, $request->user(), $data['catatan'] ?? null);
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('lembur.periksa.index')->with('sukses', "Pengajuan {$rencana->no_pengajuan} diperiksa dan diteruskan ke Operasional Manager.");
    }

    public function kembalikan(Request $request, RencanaLembur $rencana, LemburService $layanan): RedirectResponse
    {
        $data = $request->validate(
            ['catatan' => ['required', 'string', 'max:500']],
            ['catatan.required' => 'Isi catatan agar kepala unit tahu apa yang perlu diperbaiki.']
        );

        try {
            $layanan->kembalikan($rencana, $request->user(), $data['catatan']);
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('lembur.periksa.index')->with('sukses', "Pengajuan {$rencana->no_pengajuan} dikembalikan ke kepala unit.");
    }
}
