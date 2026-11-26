<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Attendance\AttendanceRules;
use App\Http\Controllers\Concerns\MemakaiBulan;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\KesalahanAbsensi;
use App\Services\AbsensiKoreksiService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KesalahanAbsensiController extends Controller
{
    use MemakaiBulan;

    /**
     * Hanya data yang belum diperbaiki admin dan belum dikonfirmasi karyawan.
     */
    private function queryPerluDiperiksa(string $awal, string $akhir): Builder
    {
        return KesalahanAbsensi::query()
            ->whereHas(
                'absensi',
                fn (Builder $q) => $q->whereBetween('tanggal_absen', [$awal, $akhir])
            )
            ->where('status_perbaikan', '!=', 'Selesai')
            ->whereHas('absensi', function (Builder $q) {
                $q->whereDoesntHave('konfirmasi', function (Builder $kq) {
                    $kq->where(function (Builder $sub) {
                        $sub->whereNotNull('status_konfir')
                            ->whereNotIn('status_konfir', ['Belum dikonfirmasi', '']);
                    });
                });
            });
    }

    public function index(Request $request)
    {
        $bulan = $this->bulanDipilih($request, $this->bulanDariTanggal(Absensi::max('tanggal_absen')));
        [$awal, $akhir] = $this->rentangBulan($bulan);

        $daftar = $this->queryPerluDiperiksa($awal, $akhir)
            ->with(['absensi.karyawan', 'absensi.konfirmasi'])
            ->when($request->filled('jenis'), fn (Builder $q) => $q->where('jenis_kesalahan', $request->input('jenis')))
            ->orderBy(
                Absensi::select('tanggal_absen')
                    ->whereColumn('absensi.id_absensi', 'kesalahan_absensi.id_absensi')
            )
            ->paginate(20)
            ->withQueryString();

        return view('admin.kesalahan.index', [
            'daftar' => $daftar,
            'bulan' => $bulan,
            'jenisPilihan' => KesalahanAbsensi::query()->distinct()->orderBy('jenis_kesalahan')->pluck('jenis_kesalahan'),
        ]);
    }

    public function konfirmasi(Absensi $absensi)
    {
        $absensi->load(['karyawan', 'jadwal.shift', 'kesalahan', 'konfirmasi']);

        return view('admin.kesalahan.konfirmasi', [
            'absensi' => $absensi,
            'statusPilihan' => AttendanceRules::statusPilihan(),
            'selesai' => $absensi->kesalahan->isNotEmpty()
                && $absensi->kesalahan->every(fn ($k) => $k->status_perbaikan === 'Selesai'),
        ]);
    }

    public function simpan(Request $request, Absensi $absensi, AbsensiKoreksiService $layanan): RedirectResponse
    {
        $data = $request->validate([
            'status_kehadiran' => ['required', Rule::in(AttendanceRules::statusPilihan())],
            'jam_masuk' => ['nullable', 'date_format:H:i'],
            'jam_pulang' => ['nullable', 'date_format:H:i'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'status_kehadiran.required' => 'Pilih status kehadiran.',
        ]);

        try {
            $layanan->tetapkanStatus(
                $absensi,
                $data['status_kehadiran'],
                $data['jam_masuk'] ?? null,
                $data['jam_pulang'] ?? null,
                $data['catatan'] ?? null,
                $request->user()->id_pengguna
            );
        } catch (DomainException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }

        return redirect()
            ->route('admin.kesalahan.index', ['bulan' => $absensi->tanggal_absen->format('Y-m')])
            ->with('sukses', 'Status kehadiran berhasil ditetapkan.');
    }
}