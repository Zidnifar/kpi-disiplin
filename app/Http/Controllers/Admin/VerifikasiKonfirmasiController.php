<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\MemakaiBulan;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\KonfirmasiAbsensi;
use App\Services\AbsensiKoreksiService;
use App\Support\Format;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifikasiKonfirmasiController extends Controller
{
    use MemakaiBulan;

    public function index(Request $request)
    {
        $bulan = $this->bulanDipilih($request, $this->bulanDariTanggal(Absensi::max('tanggal_absen')));
        [$awal, $akhir] = $this->rentangBulan($bulan);

        $dasar = fn () => KonfirmasiAbsensi::whereHas(
            'absensi',
            fn ($q) => $q->whereBetween('tanggal_absen', [$awal, $akhir])
        );

        $ringkasan = [
            'menunggu' => $dasar()->where('status_konfir', AbsensiKoreksiService::MENUNGGU)->count(),
            'terverifikasi' => $dasar()->where('status_konfir', AbsensiKoreksiService::TERVERIFIKASI)->count(),
            'ditolak' => $dasar()->where('status_konfir', AbsensiKoreksiService::DITOLAK)->count(),
        ];

        $daftar = $dasar()
            ->with('absensi.karyawan')
            ->when($request->filled('status'), fn ($q) => $q->where('status_konfir', $request->input('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $kata = Format::like((string) $request->input('q'));
                $q->whereHas('absensi.karyawan', fn ($k) => $k->where('nama_karyawan', 'like', $kata)->orWhere('nip', 'like', $kata));
            })
            ->orderByRaw("case when status_konfir = 'Menunggu verifikasi' then 0 else 1 end")
            ->orderByDesc('tanggal_konfir')
            ->paginate(20)
            ->withQueryString();

        return view('admin.verifikasi.index', compact('daftar', 'ringkasan', 'bulan'));
    }

    public function show(KonfirmasiAbsensi $konfirmasi)
    {
        $konfirmasi->load(['absensi.karyawan', 'absensi.jadwal.shift', 'pemverifikasi.karyawan']);

        return view('admin.verifikasi.show', ['konfirmasi' => $konfirmasi, 'absensi' => $konfirmasi->absensi]);
    }

    public function verifikasi(Request $request, KonfirmasiAbsensi $konfirmasi, AbsensiKoreksiService $layanan): RedirectResponse
    {
        $data = $request->validate(['catatan_verifikasi' => ['nullable', 'string', 'max:500']]);

        try {
            $layanan->verifikasi($konfirmasi, $request->user()->id_pengguna, $data['catatan_verifikasi'] ?? null);
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('admin.verifikasi.index')->with('sukses', 'Konfirmasi kehadiran diverifikasi dan data absensi diperbarui.');
    }

    public function tolak(Request $request, KonfirmasiAbsensi $konfirmasi, AbsensiKoreksiService $layanan): RedirectResponse
    {
        $data = $request->validate(
            ['catatan_verifikasi' => ['required', 'string', 'max:500']],
            ['catatan_verifikasi.required' => 'Isi catatan agar karyawan tahu alasan penolakan.']
        );

        try {
            $layanan->tolak($konfirmasi, $request->user()->id_pengguna, $data['catatan_verifikasi']);
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('admin.verifikasi.index')->with('sukses', 'Konfirmasi ditolak. Karyawan dapat mengirim ulang.');
    }
}
