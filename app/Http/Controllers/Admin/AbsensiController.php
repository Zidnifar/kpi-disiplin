<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Attendance\AttendanceRules;
use App\Http\Controllers\Concerns\MemakaiBulan;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\KesalahanAbsensi;
use App\Services\AbsensiImportService;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

class AbsensiController extends Controller
{
    use MemakaiBulan;

    private const POLA_TOKEN = '#^impor-absensi-tmp/[A-Za-z0-9]{20,64}\.[A-Za-z0-9]{2,5}$#';

    public function index(Request $request)
    {
        $bulan = $this->bulanDipilih($request, $this->bulanDariTanggal(Absensi::max('tanggal_absen')));
        [$awal, $akhir] = $this->rentangBulan($bulan);

        $dasar = fn () => Absensi::whereBetween('tanggal_absen', [$awal, $akhir]);

        $ringkasan = [
            'total' => $dasar()->count(),
            'valid' => $dasar()->where('status_kehadiran', '!=', AttendanceRules::PERLU_DICEK)->count(),
            'cek' => $dasar()->where('status_kehadiran', AttendanceRules::PERLU_DICEK)->count(),
            'belum' => KesalahanAbsensi::where('status_perbaikan', '!=', 'Selesai')
                ->whereIn('id_absensi', $dasar()->select('id_absensi'))
                ->distinct()
                ->count('id_absensi'),
        ];

        $daftar = Absensi::with(['karyawan', 'jadwal.shift'])
            ->whereBetween('tanggal_absen', [$awal, $akhir])
            ->when($request->filled('q'), function ($q) use ($request) {
                $kata = Format::like((string) $request->input('q'));
                $q->whereHas('karyawan', fn ($k) => $k->where('nama_karyawan', 'like', $kata)->orWhere('nip', 'like', $kata));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status_kehadiran', $request->input('status')))
            ->orderBy('tanggal_absen')
            ->orderBy(\App\Models\Karyawan::select('nama_karyawan')->whereColumn('karyawan.id_karyawan', 'absensi.id_karyawan'))
            ->paginate(20)
            ->withQueryString();

        return view('admin.absensi.index', [
            'daftar' => $daftar,
            'ringkasan' => $ringkasan,
            'bulan' => $bulan,
            'statusPilihan' => array_merge(AttendanceRules::statusPilihan(), [AttendanceRules::PERLU_DICEK]),
        ]);
    }

    public function formImpor()
    {
        return view('admin.absensi.impor', ['pratinjau' => null]);
    }

    /** Langkah 1: unggah file, tampilkan 5 baris pertama. Belum ada yang disimpan ke database. */
    public function pratinjau(Request $request, AbsensiImportService $layanan)
    {
        $request->validate([
            'periode' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'berkas' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ], [
            'periode.required' => 'Pilih bulan data absensi.',
            'periode.regex' => 'Periode harus berformat bulan, misalnya 2026-10.',
            'berkas.required' => 'Pilih file rekap absensi dari mesin.',
            'berkas.mimes' => 'File harus berformat .xlsx, .xls, atau .csv.',
            'berkas.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $berkas = $request->file('berkas');
        $simpan = $berkas->store('impor-absensi-tmp');

        try {
            $pratinjau = $layanan->pratinjau(Storage::path($simpan));
        } catch (Throwable $e) {
            Storage::delete($simpan);
            $pesan = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'File tidak dapat dibaca. Pastikan formatnya benar dan tidak rusak.';

            return back()->withInput()->withErrors(['berkas' => $pesan]);
        }

        return view('admin.absensi.impor', [
            'pratinjau' => $pratinjau,
            'periode' => $request->input('periode'),
            'token' => $simpan,
            'namaFile' => $berkas->getClientOriginalName(),
        ]);
    }

    /** Langkah 2: impor sungguhan dari file sementara. */
    public function proses(Request $request, AbsensiImportService $layanan): RedirectResponse
    {
        $data = $request->validate([
            'periode' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'token' => ['required', 'regex:' . self::POLA_TOKEN],
            'nama_file' => ['required', 'string', 'max:255'],
        ]);

        if (! Storage::exists($data['token'])) {
            return redirect()->route('admin.absensi.impor')
                ->with('galat', 'File sementara tidak ditemukan. Silakan unggah ulang.');
        }

        try {
            $hasil = $layanan->import(
                Storage::path($data['token']),
                $data['periode'],
                $request->user()->id_pengguna,
                $data['nama_file']
            );
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.absensi.impor')->with('galat', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('admin.absensi.impor')->with('galat', 'Impor gagal diproses. Tidak ada data yang tersimpan.');
        } finally {
            Storage::delete($data['token']);
        }

        $pesan = sprintf(
            'Impor selesai: %d absensi baru, %d diperbarui, %d kesalahan perlu diperiksa.',
            $hasil['absensi_dibuat'],
            $hasil['absensi_diperbarui'],
            $hasil['kesalahan_dicatat']
        );
        $redirect = redirect()->route('admin.absensi.index', ['bulan' => $data['periode']])->with('sukses', $pesan);

        if ($hasil['tidak_cocok'] !== []) {
            $contoh = implode(', ', array_slice($hasil['tidak_cocok'], 0, 8));
            $lebih = count($hasil['tidak_cocok']) > 8 ? ' dan lainnya' : '';
            $redirect->with('peringatan', count($hasil['tidak_cocok']) . ' NIP/nama di file tidak cocok dengan data karyawan aktif: ' . $contoh . $lebih . '.');
        }

        return $redirect;
    }
}
