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

/** Tahap 2 persetujuan: Operasional Manager menandai pengajuan yang sudah diperiksa sebagai diketahui. */
class KetahuiController extends Controller
{
    use DaftarLembur;

    public function index(Request $request)
    {
        $dasar = fn () => RencanaLembur::whereHas('persetujuan', fn ($q) => $q->where('tahap', 'periksa')->where('status_persetujuan', 'Diperiksa'));

        $ringkasan = [
            'menunggu' => $dasar()->where('status_rencana', RencanaLembur::MENUNGGU_OPERASIONAL)->count(),
            'diketahui' => $dasar()->whereHas('persetujuan', fn ($q) => $q->where('tahap', 'ketahui'))->count(),
            'bulanIni' => $dasar()->whereBetween('tanggal_lembur', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->count(),
        ];

        $query = $this->filterLembur($dasar(), $request);
        match ($request->input('status')) {
            'menunggu' => $query->where('status_rencana', RencanaLembur::MENUNGGU_OPERASIONAL),
            'diketahui' => $query->whereHas('persetujuan', fn ($q) => $q->where('tahap', 'ketahui')),
            default => null,
        };

        return view('lembur.ketahui.index', [
            'daftar' => $query->paginate(15)->withQueryString(),
            'ringkasan' => $ringkasan,
            'unit' => Departemen::orderBy('nama_dept')->get(),
        ]);
    }

    public function show(RencanaLembur $rencana)
    {
        $this->pastikanSudahDiperiksa($rencana);

        return view('lembur.ketahui.show', ['rencana' => $this->muatDetailLembur($rencana)]);
    }

    public function tandai(Request $request, RencanaLembur $rencana, LemburService $layanan): RedirectResponse
    {
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:500']]);

        try {
            $layanan->ketahui($rencana, $request->user(), $data['catatan'] ?? null);
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('lembur.ketahui.index')->with('sukses', "Pengajuan {$rencana->no_pengajuan} ditandai diketahui dan diteruskan ke General Manager.");
    }

    private function pastikanSudahDiperiksa(RencanaLembur $rencana): void
    {
        abort_unless($rencana->persetujuan()->where('tahap', 'periksa')->where('status_persetujuan', 'Diperiksa')->exists(), 404);
    }
}
