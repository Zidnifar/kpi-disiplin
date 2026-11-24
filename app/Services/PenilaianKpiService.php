<?php

namespace App\Services;

use App\Domain\Attendance\AttendanceRules;
use App\Models\Absensi;
use App\Models\HasilKpi;
use App\Models\IndikatorKpi;
use App\Models\KesalahanAbsensi;
use App\Models\PelanggaranKaryawan;
use App\Models\PeriodePenilaian;
use App\Models\PredikatNilai;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/** Data untuk halaman Penilaian KPI: kesiapan perhitungan dan tabel hasil per bulan. */
class PenilaianKpiService
{
    public function __construct(private KpiService $kpi)
    {
    }

    /** @return array<string,mixed> */
    public function data(PeriodePenilaian $periode): array
    {
        $awal = $periode->tanggal_mulai->toDateString();
        $akhir = $periode->tanggal_selesai->toDateString();

        $jumlahAbsensi = Absensi::whereBetween('tanggal_absen', [$awal, $akhir])->count();
        $aturanSiap = IndikatorKpi::where('status_indikator', 'Aktif')->exists()
            && PredikatNilai::where('status_predikat', 'Aktif')->exists();

        $belumKonfirmasi = KesalahanAbsensi::where('status_perbaikan', '!=', 'Selesai')
            ->whereIn('id_absensi', Absensi::whereBetween('tanggal_absen', [$awal, $akhir])->select('id_absensi'))
            ->count();
        $karyawanBelum = Absensi::whereBetween('tanggal_absen', [$awal, $akhir])
            ->whereIn('id_absensi', KesalahanAbsensi::where('status_perbaikan', '!=', 'Selesai')->select('id_absensi'))
            ->distinct()
            ->count('id_karyawan');

        // nama bulan dalam periode, mis. [1 => 'Januari', 2 => 'Februari', 3 => 'Maret']
        $bulan = [];
        $cursor = Carbon::parse($awal)->startOfMonth();
        while ($cursor->lte(Carbon::parse($akhir))) {
            $bulan[(int) $cursor->format('n')] = $cursor->translatedFormat('F');
            $cursor->addMonth();
        }

        $detail = $this->rincianPerBulan($awal, $akhir);
        $hasil = HasilKpi::with('predikat')->where('id_periode', $periode->id_periode)->get()->keyBy('id_karyawan');

        $pratinjau = $this->kpi->pratinjau($periode);
        // muat departemen sekali untuk semua karyawan (dipakai di pop up detail)
        EloquentCollection::make(array_column($pratinjau, 'karyawan'))->load('departemen');

        $baris = [];
        foreach ($pratinjau as $r) {
            $k = $r['karyawan'];
            $h = $hasil->get($k->id_karyawan);
            $baris[] = [
                'karyawan' => $k,
                'bulan' => $detail['poin'][$k->id_karyawan] ?? [],
                'rincian' => $detail['kehadiran'][$k->id_karyawan] ?? [],
                'rincian_poin' => $detail['daftar'][$k->id_karyawan] ?? [],
                'nilai' => $h ? (float) $h->nilai_triwulan : $r['nilai'],
                'predikat' => $h ? $h->predikat?->nama_predikat : $r['predikat'],
                'hasil' => $h,
            ];
        }

        return [
            'kesiapan' => [
                'absensi' => $jumlahAbsensi,
                'aturan' => $aturanSiap,
                'belum' => $belumKonfirmasi,
                'karyawanBelum' => $karyawanBelum,
            ],
            'bisaHitung' => $jumlahAbsensi > 0 && $aturanSiap,
            'bulan' => $bulan,
            'baris' => $baris,
            'sudahDihitung' => $hasil->isNotEmpty(),
        ];
    }

    /**
     * Rincian per karyawan per bulan (nomor bulan 1-12), dengan aturan yang sama seperti perhitungan nilai:
     *  - poin     : total poin pengurang          [id_karyawan][bulan] => int
     *  - daftar   : penyebab pengurangan          [id_karyawan][bulan] => [['tanggal','keterangan','poin'], ...]
     *  - kehadiran: hadir / terlambat / belum dikonfirmasi  [id_karyawan][bulan] => ['hadir','terlambat','belum']
     *
     * @return array{poin:array,daftar:array,kehadiran:array}
     */
    private function rincianPerBulan(string $awal, string $akhir): array
    {
        $indikator = IndikatorKpi::all();
        $bobotKode = $indikator->pluck('bobot_poin', 'kode_indikator');
        $bobotId = $indikator->pluck('bobot_poin', 'id_indikator');

        // nama indikator untuk keterangan; kalau kolom nama tidak ada, dipakai kode indikator
        $namaKode = [];
        $namaId = [];
        foreach ($indikator as $i) {
            $nama = $i->nama_indikator ?? $i->kode_indikator;
            $namaKode[$i->kode_indikator] = $nama;
            $namaId[$i->id_indikator] = $nama;
        }

        $belumIds = KesalahanAbsensi::where('status_perbaikan', '!=', 'Selesai')
            ->pluck('id_absensi')->flip()->all();

        $poin = [];
        $daftar = [];
        $kehadiran = [];

        $absensi = Absensi::whereBetween('tanggal_absen', [$awal, $akhir])
            ->get(['id_absensi', 'id_karyawan', 'tanggal_absen', 'status_kehadiran']);
        foreach ($absensi as $a) {
            $k = $a->id_karyawan;
            $bulan = (int) $a->tanggal_absen->format('n');

            $kehadiran[$k][$bulan] ??= ['hadir' => 0, 'terlambat' => 0, 'belum' => 0];
            if ($a->status_kehadiran === 'Hadir') {
                $kehadiran[$k][$bulan]['hadir']++;
            } elseif ($a->status_kehadiran === 'Terlambat') {
                $kehadiran[$k][$bulan]['terlambat']++;
            }
            if (isset($belumIds[$a->id_absensi])) {
                $kehadiran[$k][$bulan]['belum']++;
            }

            foreach (AttendanceRules::pelanggaranDariStatus($a->status_kehadiran) as $kode => $jumlah) {
                $p = $jumlah * (int) ($bobotKode[$kode] ?? 0);
                if ($p <= 0) {
                    continue;
                }
                $poin[$k][$bulan] = ($poin[$k][$bulan] ?? 0) + $p;
                $daftar[$k][$bulan][] = [
                    'tanggal' => $a->tanggal_absen,
                    'keterangan' => ($namaKode[$kode] ?? $kode) . ' (' . $a->status_kehadiran . ')',
                    'poin' => $p,
                ];
            }
        }

        $manual = PelanggaranKaryawan::where('sumber', '!=', 'Absensi')
            ->whereBetween('tanggal_pelanggaran', [$awal, $akhir])
            ->get(['id_karyawan', 'id_indikator', 'tanggal_pelanggaran', 'sumber', 'keterangan']);
        foreach ($manual as $m) {
            $p = (int) ($bobotId[$m->id_indikator] ?? 0);
            if ($p <= 0) {
                continue;
            }
            $k = $m->id_karyawan;
            $bulan = (int) $m->tanggal_pelanggaran->format('n');
            $keterangan = ($namaId[$m->id_indikator] ?? 'Pelanggaran') . ' (' . $m->sumber . ')';
            if (! empty($m->keterangan)) {
                $keterangan .= ': ' . $m->keterangan;
            }
            $poin[$k][$bulan] = ($poin[$k][$bulan] ?? 0) + $p;
            $daftar[$k][$bulan][] = [
                'tanggal' => $m->tanggal_pelanggaran,
                'keterangan' => $keterangan,
                'poin' => $p,
            ];
        }

        // urutkan kejadian per bulan dari tanggal paling awal
        foreach ($daftar as $k => $perBulan) {
            foreach ($perBulan as $bulan => $items) {
                usort($items, fn ($x, $y) => $x['tanggal']->timestamp <=> $y['tanggal']->timestamp);
                $daftar[$k][$bulan] = $items;
            }
        }

        return ['poin' => $poin, 'daftar' => $daftar, 'kehadiran' => $kehadiran];
    }
}