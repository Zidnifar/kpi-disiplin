<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\MemakaiBulan;
use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\RencanaLembur;
use App\Services\EksporExcel;
use App\Support\Format;
use Illuminate\Http\Request;

class RekapLemburController extends Controller
{
    use MemakaiBulan;

    public function index(Request $request)
    {
        [$bulan, $semua] = $this->ambil($request);

        $disetujui = $semua->filter(fn (RencanaLembur $r) => $r->disetujui());
        $ringkasan = [
            'total' => $semua->count(),
            'jam' => $disetujui->sum(fn (RencanaLembur $r) => $r->totalJamRencana()),
            'disetujui' => $disetujui->count(),
            'ditolak' => $semua->where('status_rencana', RencanaLembur::DITOLAK)->count(),
        ];

        return view('admin.lembur.index', [
            'daftar' => $semua,
            'ringkasan' => $ringkasan,
            'bulan' => $bulan,
            'unit' => Departemen::orderBy('nama_dept')->get(),
        ]);
    }

    public function excel(Request $request)
    {
        [$bulan, $semua] = $this->ambil($request);

        $baris = $semua->map(fn (RencanaLembur $r) => [
            $r->no_pengajuan,
            $r->departemen->nama_dept ?? '',
            $r->tanggal_lembur->toDateString(),
            $r->detail->count(),
            $r->totalJamRencana(),
            $r->totalJamRealisasi(),
            $r->status_rencana,
        ])->all();

        $biner = EksporExcel::buat("Rekap Lembur {$bulan}", ['No. Pengajuan', 'Unit', 'Tanggal', 'Jumlah Karyawan', 'Jam Rencana', 'Jam Realisasi', 'Status'], $baris);

        return response()->streamDownload(function () use ($biner) {
            echo $biner;
        }, "rekap-lembur-{$bulan}.xlsx", ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** @return array{0:string,1:\Illuminate\Support\Collection<int,RencanaLembur>} */
    private function ambil(Request $request): array
    {
        $bulan = $this->bulanDipilih($request, $this->bulanDariTanggal(RencanaLembur::max('tanggal_lembur')));
        [$awal, $akhir] = $this->rentangBulan($bulan);

        $semua = RencanaLembur::with(['departemen', 'detail'])
            ->where('status_rencana', '!=', RencanaLembur::DRAF)
            ->whereBetween('tanggal_lembur', [$awal, $akhir])
            ->when($request->filled('unit'), fn ($q) => $q->where('id_dept', $request->input('unit')))
            ->when($request->filled('status'), fn ($q) => $q->where('status_rencana', $request->input('status')))
            ->orderBy('tanggal_lembur')
            ->orderBy('no_pengajuan')
            ->get();

        return [$bulan, $semua];
    }
}
