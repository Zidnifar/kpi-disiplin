<?php

namespace App\Services;

use App\Domain\Attendance\AttendanceRules;
use App\Domain\Kpi\KpiCalculator;
use App\Models\Absensi;
use App\Models\DetailPenilaianKpi;
use App\Models\HasilKpi;
use App\Models\IndikatorKpi;
use App\Models\Karyawan;
use App\Models\KesalahanAbsensi;
use App\Models\NilaiSemester;
use App\Models\PelanggaranKaryawan;
use App\Models\PeriodePenilaian;
use App\Models\Pengguna;
use App\Models\PredikatNilai;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Perhitungan KPI per triwulan dan semester.
 *
 *   nilai_triwulan = 100 - SUM(frekuensi x bobot_poin)
 *   nilai_semester = (triwulan pertama + triwulan kedua) / 2   (dihitung setelah kedua triwulan tervalidasi)
 */
class KpiService
{
    public const PERIKSA_BELUM = 'Belum diperiksa';
    public const PERIKSA_SUDAH = 'Sudah diperiksa';
    public const VALIDASI_MENUNGGU = 'Menunggu validasi';
    public const VALIDASI_OK = 'Tervalidasi';
    public const VALIDASI_DIKEMBALIKAN = 'Dikembalikan';

    public function sinkronPelanggaranDariAbsensi(PeriodePenilaian $periode): int
    {
        $idIndikator = IndikatorKpi::pluck('id_indikator', 'kode_indikator');
        $absensi = Absensi::whereBetween('tanggal_absen', [
            $periode->tanggal_mulai->toDateString(),
            $periode->tanggal_selesai->toDateString(),
        ])->get();

        PelanggaranKaryawan::where('sumber', 'Absensi')
            ->whereIn('id_absensi', $absensi->pluck('id_absensi'))
            ->delete();

        $baris = [];
        foreach ($absensi as $a) {
            foreach (AttendanceRules::pelanggaranDariStatus($a->status_kehadiran) as $kode => $jumlah) {
                if (! isset($idIndikator[$kode])) {
                    throw new RuntimeException("Indikator dengan kode {$kode} belum ada. Jalankan seeder atau tambahkan di Pengaturan KPI.");
                }
                for ($i = 0; $i < $jumlah; $i++) {
                    $baris[] = [
                        'id_pelanggaran' => (string) Str::uuid(),
                        'id_karyawan' => $a->id_karyawan,
                        'id_indikator' => $idIndikator[$kode],
                        'id_absensi' => $a->id_absensi,
                        'id_pencatat' => null,
                        'tanggal_pelanggaran' => $a->tanggal_absen->toDateString(),
                        'sumber' => 'Absensi',
                        'keterangan' => $a->status_kehadiran,
                    ];
                }
            }
        }
        foreach (array_chunk($baris, 500) as $potongan) {
            PelanggaranKaryawan::insert($potongan);
        }

        return count($baris);
    }

    /**
     * Menghitung hasil KPI seluruh karyawan aktif untuk satu periode (triwulan).
     * Hasil yang sudah tervalidasi tidak ditimpa.
     *
     * @return array{dihitung:int, dilewati_tervalidasi:int, pelanggaran_dari_absensi:int}
     */
    public function hitungTriwulan(string $idPeriode, bool $paksa = false): array
    {
        return DB::transaction(function () use ($idPeriode, $paksa) {
            $periode = PeriodePenilaian::findOrFail($idPeriode);
            $awal = $periode->tanggal_mulai->toDateString();
            $akhir = $periode->tanggal_selesai->toDateString();

            $belumDiperbaiki = KesalahanAbsensi::whereIn(
                'id_absensi',
                Absensi::whereBetween('tanggal_absen', [$awal, $akhir])->select('id_absensi')
            )->where('status_perbaikan', '!=', 'Selesai')->count();

            if ($belumDiperbaiki > 0 && ! $paksa) {
                throw new DomainException(
                    "Masih ada {$belumDiperbaiki} kesalahan absensi yang belum diperbaiki pada periode ini."
                );
            }

            $dariAbsensi = $this->sinkronPelanggaranDariAbsensi($periode);
            $indikator = IndikatorKpi::all()->keyBy('id_indikator');
            $daftarPredikat = $this->daftarPredikat();
            $ringkasan = ['dihitung' => 0, 'dilewati_tervalidasi' => 0, 'pelanggaran_dari_absensi' => $dariAbsensi];

            foreach (Karyawan::where('status_karyawan', 'Aktif')->get() as $karyawan) {
                $hasil = HasilKpi::where('id_karyawan', $karyawan->id_karyawan)
                    ->where('id_periode', $idPeriode)
                    ->first();
                if ($hasil && $hasil->status_validasi === self::VALIDASI_OK) {
                    $ringkasan['dilewati_tervalidasi']++;
                    continue;
                }

                $kejadian = PelanggaranKaryawan::where('id_karyawan', $karyawan->id_karyawan)
                    ->whereBetween('tanggal_pelanggaran', [$awal, $akhir])
                    ->get()
                    ->groupBy('id_indikator');

                $items = [];
                foreach ($kejadian as $idIndikator => $daftar) {
                    // kriteria Nonaktif tidak ikut dihitung
                    if (($indikator[$idIndikator]->status_indikator ?? 'Aktif') !== 'Aktif') {
                        continue;
                    }
                    $items[] = [
                        'id_indikator' => $idIndikator,
                        'frekuensi' => $daftar->count(),
                        'bobot_poin' => (int) $indikator[$idIndikator]->bobot_poin,
                    ];
                }

                $total = KpiCalculator::totalPengurang($items);
                $nilai = KpiCalculator::nilaiTriwulan($total);
                $predikat = KpiCalculator::predikat($nilai, $daftarPredikat);

                $data = [
                    'id_predikat' => $predikat['id'] ?? null,
                    'id_pemeriksa' => null,
                    'id_pemvalidasi' => null,
                    'nilai_awal' => KpiCalculator::NILAI_AWAL,
                    'total_pengurang' => $total,
                    'nilai_triwulan' => $nilai,
                    'status_periksa' => self::PERIKSA_BELUM,
                    'status_validasi' => self::VALIDASI_MENUNGGU,
                    'tanggal_penilaian' => now(),
                    'tanggal_periksa' => null,
                    'tanggal_validasi' => null,
                    'catatan_periksa' => null,
                    'catatan_validasi' => null,
                ];

                if ($hasil) {
                    $hasil->update($data);
                } else {
                    $hasil = HasilKpi::create(
                        ['id_karyawan' => $karyawan->id_karyawan, 'id_periode' => $idPeriode] + $data
                    );
                }

                DetailPenilaianKpi::where('id_hasil', $hasil->id_hasil)->delete();
                foreach ($items as $item) {
                    DetailPenilaianKpi::create([
                        'id_hasil' => $hasil->id_hasil,
                        'id_indikator' => $item['id_indikator'],
                        'frekuensi' => $item['frekuensi'],
                        'bobot_poin' => $item['bobot_poin'],
                        'total_pengurang' => $item['frekuensi'] * $item['bobot_poin'],
                    ]);
                }
                $ringkasan['dihitung']++;
            }

            $periode->update(['status_periode' => 'Sudah dihitung']);

            return $ringkasan;
        });
    }

    /**
     * Pratinjau perhitungan satu periode tanpa menyimpan apa pun (untuk halaman Hitung Nilai KPI).
     * Cara hitungnya sama dengan hitungTriwulan(): absensi diubah menjadi pelanggaran, ditambah catatan manual/K3.
     *
     * @return array<int,array{karyawan:Karyawan, rincian:array<int,array{kode:string,frekuensi:int}>, total:int, nilai:float, predikat:?string}>
     */
    public function pratinjau(PeriodePenilaian $periode): array
    {
        $awal = $periode->tanggal_mulai->toDateString();
        $akhir = $periode->tanggal_selesai->toDateString();

        $indikator = IndikatorKpi::where('status_indikator', 'Aktif')->get();
        $bobotKode = $indikator->pluck('bobot_poin', 'kode_indikator');
        $kodeById = $indikator->pluck('kode_indikator', 'id_indikator');
        $daftarPredikat = $this->daftarPredikat();

        /** @var array<string,array<string,int>> $hitung id_karyawan => [kode => frekuensi] */
        $hitung = [];
        $absensi = Absensi::whereBetween('tanggal_absen', [$awal, $akhir])->get(['id_karyawan', 'status_kehadiran']);
        foreach ($absensi as $a) {
            foreach (AttendanceRules::pelanggaranDariStatus($a->status_kehadiran) as $kode => $jumlah) {
                $hitung[$a->id_karyawan][$kode] = ($hitung[$a->id_karyawan][$kode] ?? 0) + $jumlah;
            }
        }
        $manual = PelanggaranKaryawan::where('sumber', '!=', 'Absensi')
            ->whereBetween('tanggal_pelanggaran', [$awal, $akhir])
            ->get(['id_karyawan', 'id_indikator']);
        foreach ($manual as $m) {
            $kode = $kodeById[$m->id_indikator] ?? null;
            if ($kode !== null) {
                $hitung[$m->id_karyawan][$kode] = ($hitung[$m->id_karyawan][$kode] ?? 0) + 1;
            }
        }

        $baris = [];
        foreach (Karyawan::where('status_karyawan', 'Aktif')->orderBy('nama_karyawan')->get() as $karyawan) {
            $kejadian = $hitung[$karyawan->id_karyawan] ?? [];
            ksort($kejadian);
            $items = [];
            $rincian = [];
            foreach ($kejadian as $kode => $frekuensi) {
                $items[] = ['frekuensi' => $frekuensi, 'bobot_poin' => (int) ($bobotKode[$kode] ?? 0)];
                $rincian[] = ['kode' => $kode, 'frekuensi' => $frekuensi];
            }
            $total = KpiCalculator::totalPengurang($items);
            $nilai = KpiCalculator::nilaiTriwulan($total);
            $baris[] = [
                'karyawan' => $karyawan,
                'rincian' => $rincian,
                'total' => $total,
                'nilai' => $nilai,
                'predikat' => KpiCalculator::predikat($nilai, $daftarPredikat)['nama'] ?? null,
            ];
        }

        return $baris;
    }

    /**
     * Apakah hasil ini harus diperiksa kepala unit sebelum divalidasi admin?
     * Tidak wajib bila karyawannya bukan berperan "karyawan" (mis. kepala unit sendiri) atau departemennya
     * tidak punya kepala unit aktif, supaya hasilnya tidak macet tanpa pemeriksa.
     */
    public function perluDiperiksa(HasilKpi $hasil): bool
    {
        $karyawan = $hasil->karyawan ?? Karyawan::find($hasil->id_karyawan);
        if (! $karyawan) {
            return false;
        }
        $akun = $karyawan->pengguna;
        if ($akun && $akun->role !== Pengguna::ROLE_KARYAWAN) {
            return false;
        }

        return Pengguna::where('role', Pengguna::ROLE_KEPALA_UNIT)
            ->where('status_akun', 'Aktif')
            ->whereHas('karyawan', fn ($q) => $q->where('id_dept', $karyawan->id_dept))
            ->exists();
    }

    /** Jumlah kesalahan absensi yang belum selesai pada rentang periode. */
    public function kesalahanBelumSelesai(PeriodePenilaian $periode): int
    {
        return KesalahanAbsensi::whereIn(
            'id_absensi',
            Absensi::whereBetween('tanggal_absen', [
                $periode->tanggal_mulai->toDateString(),
                $periode->tanggal_selesai->toDateString(),
            ])->select('id_absensi')
        )->where('status_perbaikan', '!=', 'Selesai')->count();
    }

    /** Kepala unit menandai hasil sudah diperiksa. */
    public function periksa(string $idHasil, string $idPengguna, ?string $catatan = null): HasilKpi
    {
        $hasil = HasilKpi::findOrFail($idHasil);
        if ($hasil->status_validasi === self::VALIDASI_OK) {
            throw new DomainException('Hasil yang sudah tervalidasi tidak dapat diperiksa ulang.');
        }
        $hasil->update([
            'status_periksa' => self::PERIKSA_SUDAH,
            'id_pemeriksa' => $idPengguna,
            'tanggal_periksa' => now(),
            'catatan_periksa' => $catatan,
        ]);

        return $hasil->refresh();
    }

    /**
     * Admin memvalidasi hasil. Secara bawaan hasil harus sudah diperiksa kepala unit.
     * Setelah valid, nilai semester dihitung otomatis bila pasangan triwulannya juga sudah valid.
     */
    public function validasi(string $idHasil, string $idPengguna, ?string $catatan = null, bool $wajibDiperiksa = true): HasilKpi
    {
        return DB::transaction(function () use ($idHasil, $idPengguna, $catatan, $wajibDiperiksa) {
            $hasil = HasilKpi::with('periode')->findOrFail($idHasil);
            if ($wajibDiperiksa && $hasil->status_periksa !== self::PERIKSA_SUDAH) {
                throw new DomainException('Hasil KPI belum diperiksa kepala unit.');
            }

            $hasil->update([
                'status_validasi' => self::VALIDASI_OK,
                'id_pemvalidasi' => $idPengguna,
                'tanggal_validasi' => now(),
                'catatan_validasi' => $catatan,
            ]);

            $periode = $hasil->periode;
            $this->hitungSemester($hasil->id_karyawan, (int) $periode->tahun, (int) $periode->semester);

            $masihAda = HasilKpi::where('id_periode', $periode->id_periode)
                ->where('status_validasi', '!=', self::VALIDASI_OK)
                ->exists();
            if (! $masihAda) {
                $periode->update(['status_periode' => 'Selesai']);
            }

            return $hasil->refresh();
        });
    }

    /** Admin mengembalikan hasil untuk dihitung ulang. */
    public function kembalikan(string $idHasil, string $idPengguna, string $catatan): HasilKpi
    {
        $hasil = HasilKpi::findOrFail($idHasil);
        $hasil->update([
            'status_validasi' => self::VALIDASI_DIKEMBALIKAN,
            'status_periksa' => self::PERIKSA_BELUM,
            'id_pemvalidasi' => $idPengguna,
            'catatan_validasi' => $catatan,
        ]);

        return $hasil->refresh();
    }

    /**
     * Nilai semester satu karyawan. Mengembalikan null bila salah satu triwulan belum ada / belum tervalidasi.
     */
    public function hitungSemester(string $idKaryawan, int $tahun, int $semester): ?NilaiSemester
    {
        [$tw1, $tw2] = KpiCalculator::triwulanDalamSemester($semester);

        $ambil = fn (int $tw) => HasilKpi::where('id_karyawan', $idKaryawan)
            ->whereHas('periode', fn ($q) => $q->where('tahun', $tahun)->where('triwulan', $tw))
            ->first();

        $pertama = $ambil($tw1);
        $kedua = $ambil($tw2);
        if (! $pertama || ! $kedua
            || $pertama->status_validasi !== self::VALIDASI_OK
            || $kedua->status_validasi !== self::VALIDASI_OK) {
            return null;
        }

        $nilai = KpiCalculator::nilaiSemester((float) $pertama->nilai_triwulan, (float) $kedua->nilai_triwulan);
        $predikat = KpiCalculator::predikat($nilai, $this->daftarPredikat());

        return NilaiSemester::updateOrCreate(
            ['id_karyawan' => $idKaryawan, 'tahun' => $tahun, 'semester' => $semester],
            [
                'id_predikat' => $predikat['id'] ?? null,
                'id_hasil_tw_pertama' => $pertama->id_hasil,
                'id_hasil_tw_kedua' => $kedua->id_hasil,
                'nilai_semester' => $nilai,
                'tanggal_hitung' => now(),
            ]
        );
    }

    /** @return array<int,array{id:string,nama:string,min:float,max:float}> */
    private function daftarPredikat(): array
    {
        return PredikatNilai::where('status_predikat', 'Aktif')->get()
            ->map(fn (PredikatNilai $p) => [
                'id' => $p->id_predikat,
                'nama' => $p->nama_predikat,
                'min' => (float) $p->nilai_min,
                'max' => (float) $p->nilai_max,
            ])
            ->all();
    }
}