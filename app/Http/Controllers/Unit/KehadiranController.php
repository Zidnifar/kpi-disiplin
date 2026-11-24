<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Concerns\LingkupKaryawan;
use App\Http\Controllers\Concerns\MemakaiBulan;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Support\Format;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KehadiranController extends Controller
{
    use LingkupKaryawan;
    use MemakaiBulan;

    public function index(Request $request)
    {
        $saya = $this->karyawanSaya();
        $idDept = $saya?->id_dept;
        $departemen = $saya?->departemen;

        $bulanFallback = now()->format('Y-m');
        $rawBulanDefault = $this->bulanDariTanggal(Absensi::max('tanggal_absen'));
        $bulanDefault = (is_string($rawBulanDefault) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $rawBulanDefault))
            ? $rawBulanDefault
            : $bulanFallback;

        $bulan = $this->bulanDipilih($request, $bulanDefault);
        if (! is_string($bulan) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) {
            $bulan = $bulanDefault;
        }

        [$awal, $akhir] = $this->rentangBulan($bulan);

        $bulanPilihan = collect();
        $dasarKalender = Carbon::createFromFormat('Y-m', $bulanDefault)->startOfMonth();
        for ($i = 0; $i < 6; $i++) {
            $dt = (clone $dasarKalender)->subMonths($i);
            $val = $dt->format('Y-m');
            $label = $dt->locale('id')->translatedFormat('F Y');
            $bulanPilihan->push(['value' => $val, 'label' => $label]);
        }

        if ($bulanPilihan->doesntContain('value', $bulan)) {
            $dtDipilih = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
            $bulanPilihan->prepend([
                'value' => $bulan,
                'label' => $dtDipilih->locale('id')->translatedFormat('F Y'),
            ]);
        }

        $labelPeriode = $bulanPilihan->firstWhere('value', $bulan)['label']
            ?? Carbon::createFromFormat('Y-m', $bulan)->locale('id')->translatedFormat('F Y');

        $kata = trim((string) ($request->input('cari') ?? $request->input('q') ?? ''));
        $keteranganFilter = trim((string) $request->input('status', ''));

        $karyawanUnit = Karyawan::with(['departemen'])
            ->when($idDept, fn ($q) => $q->where('id_dept', $idDept))
            ->when($kata !== '', function ($q) use ($kata) {
                $pola = Format::like($kata);
                $q->where(fn ($w) => $w->where('nama_karyawan', 'like', $pola)
                    ->orWhere('nip', 'like', $pola));
            })
            ->orderBy('nama_karyawan')
            ->get();

        $idKaryawanList = $karyawanUnit->pluck('id_karyawan');

        $absensiBulan = Absensi::whereBetween('tanggal_absen', [$awal, $akhir])
            ->when($idKaryawanList->isNotEmpty(), fn ($q) => $q->whereIn('id_karyawan', $idKaryawanList))
            ->get()
            ->groupBy('id_karyawan');

        $rows = $karyawanUnit->map(function ($k) use ($absensiBulan) {
            $log = $absensiBulan->get($k->id_karyawan, collect());

            $hadir = 0;
            $terlambat = 0;
            $izinSakit = 0;
            $alpa = 0;

            foreach ($log as $abs) {
                $st = strtolower((string) ($abs->status_kehadiran ?? ''));

                if (str_contains($st, 'alpa') || str_contains($st, 'alpha') || str_contains($st, 'mangkir')) {
                    $alpa++;
                } elseif (str_contains($st, 'izin') || str_contains($st, 'sakit') || str_contains($st, 'cuti')) {
                    $izinSakit++;
                } elseif (str_contains($st, 'terlambat') || str_contains($st, 'telat')) {
                    $terlambat++;
                    $hadir++;
                } else {
                    $hadir++;
                }
            }

            $totalHari = max(0, $hadir + $izinSakit + $alpa);
            $persen = $totalHari > 0
                ? (int) round((($hadir + ($izinSakit * 0.5)) / $totalHari) * 100)
                : 0;
            $persen = max(0, min(100, $persen));

            if ($persen >= 95 && $alpa === 0) {
                $keterangan = 'Sangat baik';
            } elseif ($persen >= 88 && $alpa <= 1) {
                $keterangan = 'Baik';
            } else {
                $keterangan = 'Perlu perhatian';
            }

            return (object) [
                'id_karyawan' => $k->id_karyawan,
                'nip' => $k->nip ?? '—',
                'nama_karyawan' => $k->nama_karyawan ?? '—',
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin_sakit' => $izinSakit,
                'alpa' => $alpa,
                'persentase' => $persen,
                'keterangan' => $keterangan,
            ];
        });

        $hariKerja = (int) ($rows->max(fn ($r) => $r->hadir + $r->izin_sakit + $r->alpa) ?: 0);
        $rataPersen = $rows->isNotEmpty() ? round((float) $rows->avg('persentase'), 1) : 0.0;
        $totalTerlambat = (int) $rows->sum('terlambat');
        $totalAlpa = (int) $rows->sum('alpa');

        $ringkasan = [
            'hari_kerja' => $hariKerja,
            'rata_kehadiran' => $rataPersen,
            'total_terlambat' => $totalTerlambat,
            'total_alpa' => $totalAlpa,
        ];

        if ($keteranganFilter !== '') {
            $rows = $rows->filter(fn ($r) => strtolower($r->keterangan) === strtolower($keteranganFilter))->values();
        }

        $perPage = 7;
        $halaman = max(1, (int) $request->input('page', 1));
        $totalItem = $rows->count();
        $items = $rows->slice(($halaman - 1) * $perPage, $perPage)->values();

        $rekap = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $totalItem,
            $perPage,
            $halaman,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('unit.kehadiran', [
            'rekap' => $rekap,
            'departemen' => $departemen,
            'ringkasan' => $ringkasan,
            'bulanPilihan' => $bulanPilihan,
            'periodeDipilih' => $bulan,
            'labelPeriode' => $labelPeriode,
        ]);
    }
}