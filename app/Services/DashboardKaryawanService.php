<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\HasilKpi;
use App\Models\Karyawan;
use App\Models\KesalahanAbsensi;
use Carbon\Carbon;

/** Data untuk dashboard karyawan: kehadiran bulan terakhir, nilai KPI, data yang perlu dikonfirmasi. */
class DashboardKaryawanService
{
    /** @return array<string,mixed> */
    public function data(?Karyawan $karyawan): array
    {
        if (! $karyawan) {
            return $this->kosong();
        }
        $id = $karyawan->id_karyawan;

        // ---- Kehadiran: bulan acuan = bulan absensi terakhir milik karyawan ini
        $terakhir = Absensi::where('id_karyawan', $id)->max('tanggal_absen');
        $bulanAcuan = $terakhir ? Carbon::parse($terakhir) : now();
        $statusBulan = Absensi::where('id_karyawan', $id)
            ->whereBetween('tanggal_absen', [
                $bulanAcuan->copy()->startOfMonth()->toDateString(),
                $bulanAcuan->copy()->endOfMonth()->toDateString(),
            ])
            ->pluck('status_kehadiran');

        $terlambat = $statusBulan->filter(fn ($s) => $s === 'Terlambat')->count();
        // "hadir" = datang bekerja, termasuk yang terlambat
        $hadir = $statusBulan->filter(fn ($s) => in_array($s, ['Hadir', 'Terlambat'], true))->count();

        // ---- Nilai KPI (hanya yang sudah tervalidasi), terbaru lebih dulu
        $hasilValid = HasilKpi::with(['periode', 'predikat'])
            ->where('id_karyawan', $id)
            ->where('status_validasi', KpiService::VALIDASI_OK)
            ->get()
            ->filter(fn ($h) => $h->periode)
            ->sortByDesc(fn ($h) => ((int) $h->periode->tahun) * 10 + (int) $h->periode->triwulan)
            ->values();

        $kpiTerakhir = $hasilValid->first();
        $tahunGrafik = $kpiTerakhir ? (int) $kpiTerakhir->periode->tahun : (int) now()->year;

        $grafik = [];
        foreach ([1, 2, 3, 4] as $tw) {
            $h = $hasilValid->first(fn ($x) => (int) $x->periode->tahun === $tahunGrafik && (int) $x->periode->triwulan === $tw);
            $grafik[$tw] = $h ? (float) $h->nilai_triwulan : null;
        }

        $tahunMin = $hasilValid->isNotEmpty() ? (int) $hasilValid->min(fn ($h) => (int) $h->periode->tahun) : null;
        $tahunMax = $hasilValid->isNotEmpty() ? (int) $hasilValid->max(fn ($h) => (int) $h->periode->tahun) : null;

        // ---- Data yang perlu dikonfirmasi karyawan
        $kesalahan = KesalahanAbsensi::with(['absensi.jadwal.shift'])
            ->where('status_perbaikan', '!=', 'Selesai')
            ->whereIn('id_absensi', Absensi::where('id_karyawan', $id)->select('id_absensi'))
            ->get()
            ->filter(fn ($k) => $k->absensi)
            ->sortBy(fn ($k) => $k->absensi->tanggal_absen)
            ->values();

        return [
            'bulanLabel' => $bulanAcuan->translatedFormat('M Y'),
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'nilaiTerakhir' => $kpiTerakhir ? (float) $kpiTerakhir->nilai_triwulan : null,
            'triwulanTerakhir' => $kpiTerakhir ? (int) $kpiTerakhir->periode->triwulan : null,
            'tahunTerakhir' => $kpiTerakhir ? (int) $kpiTerakhir->periode->tahun : null,
            'kategori' => $kpiTerakhir?->predikat?->nama_predikat,
            'grafik' => $grafik,
            'tahunGrafik' => $tahunGrafik,
            'rentangRiwayat' => $tahunMin === null ? null : ($tahunMin === $tahunMax ? "Tahun {$tahunMax}" : "{$tahunMin} – {$tahunMax}"),
            'perluKonfirmasi' => $kesalahan->take(5),
            'jumlahKonfirmasi' => $kesalahan->count(),
        ];
    }

    /**
     * Jumlah tahap persetujuan lembur yang sudah selesai (0-4), dari teks status_rencana.
     * Tahap: 1 Diajukan (Kepala Unit), 2 Diperiksa (Kepala Produksi), 3 Diketahui (Operasional Manager),
     * 4 Keputusan (General Manager). Sesuaikan kata kunci di bawah dengan isi status_rencana di databasemu.
     */
    public static function langkahLembur(?string $status): int
    {
        $s = strtolower((string) $status);

        return match (true) {
            $s === '' || str_contains($s, 'draf') => 0,
            str_contains($s, 'setuju') || str_contains($s, 'tolak') || str_contains($s, 'keputusan') || str_contains($s, 'selesai') => 4,
            str_contains($s, 'diketahui') || str_contains($s, 'general') => 3,
            str_contains($s, 'diperiksa') || str_contains($s, 'operasional') => 2,
            default => 1, // diajukan / menunggu Ka. Produksi
        };
    }

    /** @return array<string,mixed> */
    private function kosong(): array
    {
        return [
            'bulanLabel' => now()->translatedFormat('M Y'),
            'hadir' => 0,
            'terlambat' => 0,
            'nilaiTerakhir' => null,
            'triwulanTerakhir' => null,
            'tahunTerakhir' => null,
            'kategori' => null,
            'grafik' => [1 => null, 2 => null, 3 => null, 4 => null],
            'tahunGrafik' => (int) now()->year,
            'rentangRiwayat' => null,
            'perluKonfirmasi' => collect(),
            'jumlahKonfirmasi' => 0,
        ];
    }
}