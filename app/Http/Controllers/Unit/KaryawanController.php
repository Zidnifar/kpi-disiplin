<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Concerns\LingkupKaryawan;
use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Support\Format;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    use LingkupKaryawan;

    public function index(Request $request)
    {
        $saya = $this->karyawanSaya();
        $idDept = $saya?->id_dept;
        $departemen = $saya?->departemen;

        $dasar = Karyawan::with(['departemen', 'pengguna'])
            ->when($idDept, fn ($q) => $q->where('id_dept', $idDept));

        $semuaUnit = (clone $dasar)->get();

        $ringkasan = [
            'total' => $semuaUnit->count(),
            'aktif' => $semuaUnit->where('status_karyawan', 'Aktif')->count(),
            'nonaktif' => $semuaUnit->where('status_karyawan', '!=', 'Aktif')->count(),
        ];

        $jabatanPilihan = $semuaUnit
            ->pluck('jabatan')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $kata = trim((string) ($request->input('cari') ?? $request->input('q') ?? ''));

        $karyawan = (clone $dasar)
            ->when($kata !== '', function ($q) use ($kata) {
                $pola = Format::like($kata);
                $q->where(fn ($w) => $w->where('nama_karyawan', 'like', $pola)
                    ->orWhere('nip', 'like', $pola)
                    ->orWhere('no_hp', 'like', $pola)
                    ->orWhere('email', 'like', $pola));
            })
            ->when($request->filled('jabatan'), fn ($q) => $q->where('jabatan', $request->input('jabatan')))
            ->when($request->filled('status'), fn ($q) => $q->where('status_karyawan', $request->input('status')))
            ->orderBy('nama_karyawan')
            ->paginate(7)
            ->withQueryString();

        return view('unit.karyawan', [
            'karyawan' => $karyawan,
            'departemen' => $departemen,
            'ringkasan' => $ringkasan,
            'jabatanPilihan' => $jabatanPilihan,
        ]);
    }

    public function show(Karyawan $karyawan)
    {
        $saya = $this->karyawanSaya();

        if ($saya?->id_dept && $karyawan->id_dept !== $saya->id_dept) {
            abort(403, 'Data karyawan berada di luar departemen Anda.');
        }

        $karyawan->load(['departemen', 'pengguna']);

        return view('unit.karyawan.show', [
            'karyawan' => $karyawan,
        ]);
    }
}