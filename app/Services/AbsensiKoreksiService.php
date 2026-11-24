<?php

namespace App\Services;

use App\Domain\Attendance\AttendanceRules;
use App\Models\Absensi;
use App\Models\KesalahanAbsensi;
use App\Models\KonfirmasiAbsensi;
use App\Models\ShiftKerja;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Alur konfirmasi kehadiran:
 *  1. karyawan mengirim konfirmasi ("data benar" atau "perlu koreksi")      -> kirimKonfirmasi()
 *  2. admin memverifikasi (data diperbarui) atau menolak                      -> verifikasi() / tolak()
 *  3. atau admin langsung menetapkan status dari halaman Pemeriksaan Kesalahan -> tetapkanStatus()
 * Absensi yang sudah punya konfirmasi / kesalahan selesai tidak ditimpa lagi oleh impor ulang.
 */
class AbsensiKoreksiService
{
    public const MENUNGGU = 'Menunggu verifikasi';
    public const TERVERIFIKASI = 'Terverifikasi';
    public const DITOLAK = 'Ditolak';

    public const JAWABAN_BENAR = 'Benar';
    public const JAWABAN_KOREKSI = 'Koreksi';

    public function kirimKonfirmasi(
        Absensi $absensi,
        string $jawaban,
        ?string $jamMasukKoreksi,
        ?string $jamPulangKoreksi,
        ?string $alasan
    ): KonfirmasiAbsensi {
        if (! in_array($jawaban, [self::JAWABAN_BENAR, self::JAWABAN_KOREKSI], true)) {
            throw new DomainException('Jawaban konfirmasi tidak dikenal.');
        }

        return DB::transaction(function () use ($absensi, $jawaban, $jamMasukKoreksi, $jamPulangKoreksi, $alasan) {
            $ada = KonfirmasiAbsensi::where('id_absensi', $absensi->id_absensi)->lockForUpdate()->first();
            if ($ada && $ada->status_konfir !== self::DITOLAK) {
                throw new DomainException('Data kehadiran ini sudah dikonfirmasi.');
            }

            $koreksi = $jawaban === self::JAWABAN_KOREKSI;
            $data = [
                'tanggal_konfir' => now(),
                'jawaban_konfir' => $jawaban,
                'jam_masuk_koreksi' => $koreksi ? $this->jam($jamMasukKoreksi) : null,
                'jam_pulang_koreksi' => $koreksi ? $this->jam($jamPulangKoreksi) : null,
                'alasan' => $alasan,
                'status_konfir' => self::MENUNGGU,
                'id_pemverifikasi' => null,
                'tanggal_verifikasi' => null,
                'catatan_verifikasi' => null,
            ];

            if ($ada) {
                $ada->update($data);

                return $ada->refresh();
            }

            return KonfirmasiAbsensi::create(['id_absensi' => $absensi->id_absensi] + $data);
        });
    }

    /** Admin menyetujui konfirmasi: jam koreksi diterapkan, status dihitung ulang, kesalahan diselesaikan. */
    public function verifikasi(KonfirmasiAbsensi $konfirmasi, string $idPengguna, ?string $catatan = null): void
    {
        DB::transaction(function () use ($konfirmasi, $idPengguna, $catatan) {
            $konfir = KonfirmasiAbsensi::lockForUpdate()->findOrFail($konfirmasi->id_konfir);
            if ($konfir->status_konfir !== self::MENUNGGU) {
                throw new DomainException('Konfirmasi ini sudah diproses.');
            }

            $absensi = Absensi::with('jadwal.shift')->findOrFail($konfir->id_absensi);
            $koreksi = $konfir->jawaban_konfir === self::JAWABAN_KOREKSI;
            $masuk = ($koreksi && $konfir->jam_masuk_koreksi) ? $konfir->jam_masuk_koreksi : $absensi->jam_masuk;
            $pulang = ($koreksi && $konfir->jam_pulang_koreksi) ? $konfir->jam_pulang_koreksi : $absensi->jam_pulang;

            $shift = $this->shiftUntuk($absensi, $masuk, $pulang);
            if ($shift === null) {
                throw new DomainException('Shift untuk data ini tidak ditemukan. Tambahkan shift kerja lebih dulu.');
            }

            $hasil = AttendanceRules::evaluasi(
                $masuk,
                $pulang,
                $shift->jam_mulai,
                $shift->jam_selesai,
                (int) $shift->toleransi_menit,
                (int) $shift->batas_pulang_awal_menit
            );
            if ($hasil['masalah'] !== []) {
                throw new DomainException(
                    'Jam masuk atau jam pulang masih kosong. Tetapkan status kehadiran lewat menu Pemeriksaan Kesalahan.'
                );
            }

            $absensi->update([
                'jam_masuk' => $masuk,
                'jam_pulang' => $pulang,
                'status_kehadiran' => $hasil['status'],
                'keterangan_absen' => null,
            ]);
            $this->selesaikanKesalahan($absensi->id_absensi, $idPengguna, $catatan ?? 'Diselesaikan lewat verifikasi konfirmasi karyawan');

            $konfir->update([
                'status_konfir' => self::TERVERIFIKASI,
                'id_pemverifikasi' => $idPengguna,
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $catatan,
            ]);
        });
    }

    public function tolak(KonfirmasiAbsensi $konfirmasi, string $idPengguna, string $catatan): void
    {
        DB::transaction(function () use ($konfirmasi, $idPengguna, $catatan) {
            $konfir = KonfirmasiAbsensi::lockForUpdate()->findOrFail($konfirmasi->id_konfir);
            if ($konfir->status_konfir !== self::MENUNGGU) {
                throw new DomainException('Konfirmasi ini sudah diproses.');
            }
            $konfir->update([
                'status_konfir' => self::DITOLAK,
                'id_pemverifikasi' => $idPengguna,
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $catatan,
            ]);
        });
    }

    /** Admin menetapkan status akhir (dan jam final, bila perlu) untuk absensi bermasalah. */
    public function tetapkanStatus(
        Absensi $absensi,
        string $status,
        ?string $jamMasuk,
        ?string $jamPulang,
        ?string $catatan,
        string $idPengguna
    ): void {
        if (! in_array($status, AttendanceRules::statusPilihan(), true)) {
            throw new DomainException('Status kehadiran tidak valid.');
        }

        DB::transaction(function () use ($absensi, $status, $jamMasuk, $jamPulang, $catatan, $idPengguna) {
            $absensi->update([
                'jam_masuk' => $this->jam($jamMasuk),
                'jam_pulang' => $this->jam($jamPulang),
                'status_kehadiran' => $status,
                'keterangan_absen' => null,
            ]);
            $this->selesaikanKesalahan($absensi->id_absensi, $idPengguna, $catatan);

            // konfirmasi karyawan yang masih menunggu ikut dianggap selesai agar tidak menggantung
            KonfirmasiAbsensi::where('id_absensi', $absensi->id_absensi)
                ->where('status_konfir', self::MENUNGGU)
                ->update([
                    'status_konfir' => self::TERVERIFIKASI,
                    'id_pemverifikasi' => $idPengguna,
                    'tanggal_verifikasi' => now(),
                    'catatan_verifikasi' => 'Diselesaikan lewat Pemeriksaan Kesalahan',
                ]);
        });
    }

    private function selesaikanKesalahan(string $idAbsensi, string $idPengguna, ?string $catatan): void
    {
        KesalahanAbsensi::where('id_absensi', $idAbsensi)
            ->where('status_perbaikan', '!=', 'Selesai')
            ->update([
                'status_perbaikan' => 'Selesai',
                'id_pengguna' => $idPengguna,
                'catatan' => $catatan,
                'tanggal_perbaikan' => now(),
            ]);
    }

    /** Shift dari jadwal; bila tidak ada, shift aktif yang jam mulainya paling dekat dengan jam masuk/pulang. */
    private function shiftUntuk(Absensi $absensi, ?string $masuk, ?string $pulang): ?ShiftKerja
    {
        if ($absensi->jadwal?->shift) {
            return $absensi->jadwal->shift;
        }

        $acuan = $masuk ?? $pulang;
        $semua = ShiftKerja::where('status_shift', 'Aktif')->get();
        if ($acuan === null) {
            return $semua->first();
        }

        $detik = AttendanceRules::detik($acuan);

        return $semua
            ->sortBy(fn (ShiftKerja $s) => min(
                abs($detik - AttendanceRules::detik($s->jam_mulai)),
                abs($detik - AttendanceRules::detik($s->jam_selesai))
            ))
            ->first();
    }

    /** "07:04" -> "07:04:00"; kosong -> null. */
    private function jam(?string $jam): ?string
    {
        if ($jam === null || trim($jam) === '') {
            return null;
        }
        $jam = trim($jam);

        return strlen($jam) === 5 ? $jam . ':00' : $jam;
    }
}
