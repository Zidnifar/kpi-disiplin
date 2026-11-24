<?php

namespace App\Http\Controllers\Saya;

use App\Domain\Attendance\AttendanceRules;
use App\Http\Controllers\Concerns\LingkupKaryawan;
use App\Http\Controllers\Concerns\MemakaiBulan;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;

class KehadiranController extends Controller
{
    use LingkupKaryawan, MemakaiBulan;

    public function index(Request $request)
    {
        $saya = $this->karyawanSaya();
        $terakhir = Absensi::where('id_karyawan', $saya->id_karyawan)->max('tanggal_absen');
        $bulan = $this->bulanDipilih($request, $this->bulanDariTanggal($terakhir));
        [$awal, $akhir] = $this->rentangBulan($bulan);

        $semua = Absensi::where('id_karyawan', $saya->id_karyawan)
            ->whereBetween('tanggal_absen', [$awal, $akhir])
            ->pluck('status_kehadiran');

        $ringkasan = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'alpa' => 0];
        foreach ($semua as $status) {
            $k = AttendanceRules::kelompok($status);
            if (isset($ringkasan[$k])) {
                $ringkasan[$k]++;
            }
        }

        $daftar = Absensi::with(['jadwal.shift', 'konfirmasi', 'kesalahan'])
            ->where('id_karyawan', $saya->id_karyawan)
            ->whereBetween('tanggal_absen', [$awal, $akhir])
            ->when($request->filled('status'), fn ($q) => $q->where('status_kehadiran', $request->input('status')))
            ->orderBy('tanggal_absen')
            ->paginate(20)
            ->withQueryString();

        return view('saya.kehadiran', [
            'daftar' => $daftar,
            'ringkasan' => $ringkasan,
            'bulan' => $bulan,
            'statusPilihan' => array_merge(AttendanceRules::statusPilihan(), [AttendanceRules::PERLU_DICEK]),
        ]);
    }
}
