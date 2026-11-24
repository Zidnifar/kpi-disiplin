<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodePenilaian;
use App\Services\KpiService;
use App\Services\PenilaianKpiService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

/** Halaman Penilaian KPI: memilih periode yang sudah dibuat, memeriksa kesiapan, lalu menjalankan perhitungan. */
class PenilaianKpiController extends Controller
{
    public function index(Request $request, PenilaianKpiService $layanan)
    {
        $periodeList = PeriodePenilaian::orderByDesc('tahun')->orderByDesc('triwulan')->get();
        $periode = $periodeList->firstWhere('id_periode', $request->input('periode')) ?? $periodeList->first();

        if (! $periode) {
            return view('admin.kpi.penilaian.index', ['periode' => null, 'periodeList' => $periodeList]);
        }

        // Ambil data dasar dari service
        $dataLayanan = $layanan->data($periode);

        // Ubah array $baris menjadi instance LengthAwarePaginator agar mendukung pagination di view
        $barisCollection = collect($dataLayanan['baris'] ?? []);
        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        
        $paginatedBaris = new LengthAwarePaginator(
            $barisCollection->forPage($currentPage, $perPage)->values(),
            $barisCollection->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Timpa key 'baris' dengan data yang sudah di-paginate
        $dataLayanan['baris'] = $paginatedBaris;

        return view('admin.kpi.penilaian.index', [
            'periode' => $periode, 
            'periodeList' => $periodeList
        ] + $dataLayanan);
    }

    public function show(HasilKpi $hasil) // sesuaikan parameter model dengan route-mu
    {
    $hasil->load(['karyawan.departemen', 'periode', 'predikat', 'detail']);

    return view('admin.kpi.penilaian.show', compact('hasil'));
    }   
    
    public function hitung(Request $request, KpiService $kpi): RedirectResponse
    {
        $data = $request->validate([
            'periode' => ['required', Rule::exists('periode_penilaian', 'id_periode')],
        ], ['periode.required' => 'Pilih periode penilaian.']);

        try {
            $hasil = $kpi->hitungTriwulan($data['periode'], $request->boolean('paksa'));
        } catch (DomainException $e) {
            return redirect()->route('admin.kpi.penilaian.index', ['periode' => $data['periode']])->with('galat', $e->getMessage());
        }

        $pesan = "Perhitungan selesai: {$hasil['dihitung']} karyawan dihitung";
        if ($hasil['dilewati_tervalidasi'] > 0) {
            $pesan .= ", {$hasil['dilewati_tervalidasi']} dilewati karena sudah tervalidasi";
        }

        return redirect()->route('admin.kpi.penilaian.index', ['periode' => $data['periode']])->with('sukses', $pesan . '.');
    }
}