<?php

namespace App\Http\Controllers\Saya;

use App\Http\Controllers\Concerns\LingkupKaryawan;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Services\AbsensiKoreksiService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KonfirmasiController extends Controller
{
    use LingkupKaryawan;

    public function index()
    {
        $saya = $this->karyawanSaya();

        $daftar = Absensi::with(['jadwal.shift', 'konfirmasi', 'kesalahan'])
            ->where('id_karyawan', $saya->id_karyawan)
            ->where(fn ($q) => $q->whereHas('kesalahan')->orWhereHas('konfirmasi'))
            ->orderByDesc('tanggal_absen')
            ->paginate(20);

        $belum = Absensi::where('id_karyawan', $saya->id_karyawan)
            ->whereHas('kesalahan', fn ($q) => $q->where('status_perbaikan', '!=', 'Selesai'))
            ->whereDoesntHave('konfirmasi')
            ->count();

        return view('saya.konfirmasi.index', compact('daftar', 'belum'));
    }

    public function form(Absensi $absensi)
    {
        $this->pastikanMilikSaya($absensi);
        $absensi->load(['jadwal.shift', 'konfirmasi', 'kesalahan']);

        return view('saya.konfirmasi.form', [
            'absensi' => $absensi,
            'bolehKirim' => $this->bolehKirim($absensi),
        ]);
    }

    public function kirim(Request $request, Absensi $absensi, AbsensiKoreksiService $layanan): RedirectResponse
    {
        $this->pastikanMilikSaya($absensi);
        $absensi->load(['konfirmasi', 'kesalahan']);

        if (! $this->bolehKirim($absensi)) {
            return redirect()->route('saya.konfirmasi')->with('galat', 'Data ini tidak dapat dikonfirmasi.');
        }

        $data = $request->validate([
            'jawaban' => ['required', Rule::in([AbsensiKoreksiService::JAWABAN_BENAR, AbsensiKoreksiService::JAWABAN_KOREKSI])],
            'jam_masuk_koreksi' => ['nullable', 'date_format:H:i'],
            'jam_pulang_koreksi' => ['nullable', 'date_format:H:i'],
            'alasan' => ['nullable', 'string', 'max:500'],
        ], [
            'jawaban.required' => 'Pilih salah satu jawaban.',
        ]);

        if ($data['jawaban'] === AbsensiKoreksiService::JAWABAN_KOREKSI) {
            if (empty($data['jam_masuk_koreksi']) && empty($data['jam_pulang_koreksi'])) {
                return back()->withInput()->withErrors(['jam_pulang_koreksi' => 'Isi jam masuk atau jam pulang yang sebenarnya.']);
            }
            if (empty($data['alasan'])) {
                return back()->withInput()->withErrors(['alasan' => 'Jelaskan alasan koreksi.']);
            }
        }

        try {
            $layanan->kirimKonfirmasi(
                $absensi,
                $data['jawaban'],
                $data['jam_masuk_koreksi'] ?? null,
                $data['jam_pulang_koreksi'] ?? null,
                $data['alasan'] ?? null
            );
        } catch (DomainException $e) {
            return redirect()->route('saya.konfirmasi')->with('galat', $e->getMessage());
        }

        return redirect()->route('saya.konfirmasi')->with('sukses', 'Konfirmasi terkirim dan menunggu verifikasi admin.');
    }

    private function pastikanMilikSaya(Absensi $absensi): void
    {
        abort_unless($absensi->id_karyawan === $this->karyawanSaya()->id_karyawan, 404);
    }

    /** Boleh dikirim bila ada kesalahan yang belum selesai dan belum ada konfirmasi (atau konfirmasi sebelumnya ditolak). */
    private function bolehKirim(Absensi $absensi): bool
    {
        $adaMasalah = $absensi->kesalahan->contains(fn ($k) => $k->status_perbaikan !== 'Selesai');
        $konfirmasi = $absensi->konfirmasi;

        if ($konfirmasi) {
            return $konfirmasi->status_konfir === AbsensiKoreksiService::DITOLAK;
        }

        return $adaMasalah;
    }
}
