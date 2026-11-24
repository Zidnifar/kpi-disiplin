<?php

namespace App\Services;

use App\Domain\Attendance\AttendanceRules;
use App\Models\Absensi;
use App\Models\ImporAbsensi;
use App\Models\JadwalShift;
use App\Models\Karyawan;
use App\Models\KesalahanAbsensi;
use App\Models\ShiftKerja;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Impor data scan mesin absensi (xlsx/xls/csv) menjadi rekap harian di tabel absensi.
 *
 * Format file yang dibaca (nama kolom dicari otomatis, huruf besar/kecil diabaikan):
 *   - pengenal karyawan : nip / pin / id
 *   - nama              : nama / name
 *   - waktu scan        : satu kolom "waktu" (tanggal + jam) ATAU kolom "tanggal" dan "jam" terpisah
 * Satu baris = satu kali scan. Beberapa scan per hari digabung: scan pertama = masuk, scan terakhir = pulang.
 */
class AbsensiImportService
{
    private const ALIAS_NIP = ['nip', 'pin', 'id', 'no id', 'no. id', 'nomor'];
    private const ALIAS_NAMA = ['nama', 'name', 'nama karyawan'];
    private const ALIAS_WAKTU = ['waktu', 'tanggal/waktu', 'tanggal waktu', 'datetime', 'waktu scan', 'scan'];
    private const ALIAS_TANGGAL = ['tanggal', 'date', 'tgl'];
    private const ALIAS_JAM = ['jam', 'time', 'jam scan'];
    private const ALIAS_SHIFT = ['shift', 'kode shift', 'nama shift', 'jadwal'];

    /** Dua scan yang jaraknya kurang dari ini dianggap satu scan (tap ganda). */
    private const MIN_JARAK_SCAN_MENIT = 60;

    public const STATUS_DIPROSES = 'Diproses';
    public const STATUS_SELESAI = 'Selesai';
    public const STATUS_PERINGATAN = 'Selesai dengan peringatan';

    /**
     * @param string $path      lokasi file di server
     * @param string $periode   bulan data, format YYYY-MM
     * @return array<string,mixed> ringkasan hasil impor
     */
    public function import(string $path, string $periode, string $idPengguna, string $namaFile): array
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
            throw new InvalidArgumentException('Periode harus berformat YYYY-MM.');
        }

        $awal = Carbon::parse($periode . '-01')->startOfMonth();
        $akhir = $awal->copy()->endOfMonth();
        $baris = $this->bacaBaris($path);

        return DB::transaction(function () use ($baris, $awal, $akhir, $periode, $idPengguna, $namaFile) {
            $impor = ImporAbsensi::create([
                'id_pengguna' => $idPengguna,
                'nama_file' => mb_substr($namaFile, 0, 100),
                'periode_impor' => $periode,
                'jumlah_baris' => count($baris),
                'status_impor' => self::STATUS_DIPROSES,
            ]);

            $ringkasan = $this->proses($baris, $awal, $akhir, $impor);
            $impor->update([
                'status_impor' => $ringkasan['tidak_cocok'] === [] ? self::STATUS_SELESAI : self::STATUS_PERINGATAN,
            ]);

            return ['id_impor' => $impor->id_impor] + $ringkasan;
        });
    }

    /**
     * Membaca file tanpa menyimpan apa pun, untuk layar pratinjau sebelum impor.
     *
     * @return array{total:int, contoh:array<int,array{nip:string,nama:string,tanggal:string,shift:string,jam:string}>}
     */
    public function pratinjau(string $path, int $jumlah = 5): array
    {
        $baris = $this->bacaBaris($path);
        $contoh = [];
        foreach (array_slice($baris, 0, $jumlah) as $b) {
            $contoh[] = [
                'nip' => $b['kunci'],
                'nama' => $b['nama'],
                'tanggal' => $b['waktu']->translatedFormat('d M Y'),
                'shift' => $b['shift'],
                'jam' => $b['waktu']->format('H:i'),
            ];
        }

        return ['total' => count($baris), 'contoh' => $contoh];
    }

    /**
     * @param array<int,array{kunci:string,nama:string,shift:string,waktu:Carbon}> $baris
     * @return array<string,mixed>
     */
    private function proses(array $baris, Carbon $awal, Carbon $akhir, ImporAbsensi $impor): array
    {
        $karyawanAktif = Karyawan::where('status_karyawan', 'Aktif')->get();
        $petaNip = [];
        $petaNama = [];
        foreach ($karyawanAktif as $k) {
            if ($k->nip !== null && $k->nip !== '') {
                $petaNip[$this->normNip($k->nip)] = $k;
            }
            $petaNama[$this->normNama($k->nama_karyawan)] = $k;
        }

        $semuaShift = ShiftKerja::where('status_shift', 'Aktif')->get();
        // peta kode/nama shift (huruf kecil) -> shift, untuk kolom "Shift" di file mesin (opsional)
        $petaShift = [];
        foreach ($semuaShift as $sk) {
            $petaShift[mb_strtolower(trim($sk->kode_shift))] = $sk;
            $petaShift[mb_strtolower(trim($sk->nama_shift))] = $sk;
        }
        $shiftFile = [];
        $jadwal = JadwalShift::with('shift')
            ->whereBetween('tanggal_jadwal', [$awal->toDateString(), $akhir->toDateString()])
            ->get()
            ->keyBy(fn (JadwalShift $j) => $j->id_karyawan . '|' . $j->tanggal_jadwal->toDateString());

        $ringkasan = [
            'total_baris' => count($baris),
            'baris_di_luar_periode' => 0,
            'absensi_dibuat' => 0,
            'absensi_diperbarui' => 0,
            'dilewati_sudah_dikonfirmasi' => 0,
            'kesalahan_dicatat' => 0,
            'tidak_cocok' => [],
        ];

        // 1. kelompokkan scan per (karyawan, tanggal)
        $kelompok = [];
        foreach ($baris as $b) {
            if ($b['waktu']->lt($awal) || $b['waktu']->gt($akhir)) {
                $ringkasan['baris_di_luar_periode']++;
                continue;
            }
            $karyawan = $petaNip[$this->normNip($b['kunci'])] ?? $petaNama[$this->normNama($b['nama'])] ?? null;
            if ($karyawan === null) {
                $ringkasan['tidak_cocok'][$b['kunci'] . ' / ' . $b['nama']] = true;
                continue;
            }
            $kunciKelompok = $karyawan->id_karyawan . '|' . $b['waktu']->toDateString();
            $kelompok[$kunciKelompok][] = $b['waktu'];
            if (($b['shift'] ?? '') !== '' && ! isset($shiftFile[$kunciKelompok])) {
                $shiftFile[$kunciKelompok] = mb_strtolower($b['shift']);
            }
        }
        $ringkasan['tidak_cocok'] = array_keys($ringkasan['tidak_cocok']);

        // 2. satu rekap per kelompok
        $diproses = [];
        foreach ($kelompok as $kunci => $scan) {
            [$idKaryawan, $tanggal] = explode('|', $kunci);
            $diproses[$kunci] = true;
            usort($scan, fn (Carbon $a, Carbon $b) => $a->timestamp <=> $b->timestamp);

            $jadwalHari = $jadwal->get($kunci);
            $shiftDariFile = isset($shiftFile[$kunci]) ? ($petaShift[$shiftFile[$kunci]] ?? null) : null;
            $shift = $jadwalHari?->shift ?? $shiftDariFile ?? $this->tebakShift($scan[0], $semuaShift);
            $masalahTambahan = [];
            if ($shift === null) {
                continue; // belum ada shift sama sekali di sistem
            }
            if ($jadwalHari === null && $shiftDariFile === null) {
                $masalahTambahan[] = 'Tidak ada jadwal';
            }
            if ($jadwalHari === null && $shiftDariFile !== null) {
                // shift dibaca dari file mesin: simpan sebagai jadwal agar impor ulang konsisten
                $jadwalHari = JadwalShift::create([
                    'id_karyawan' => $idKaryawan,
                    'id_shift' => $shiftDariFile->id_shift,
                    'tanggal_jadwal' => $tanggal,
                ]);
            }
            if (count($scan) > 2) {
                $masalahTambahan[] = 'Berpotensi ganda';
            }

            [$masuk, $pulang] = $this->tentukanMasukPulang($scan, $shift);
            $hasil = AttendanceRules::evaluasi(
                $masuk,
                $pulang,
                $shift->jam_mulai,
                $shift->jam_selesai,
                (int) $shift->toleransi_menit,
                (int) $shift->batas_pulang_awal_menit
            );
            $masalah = array_values(array_unique(array_merge($hasil['masalah'], $masalahTambahan)));
            $status = $masalah === [] ? $hasil['status'] : AttendanceRules::PERLU_DICEK;

            $this->simpanAbsensi($idKaryawan, $tanggal, $jadwalHari?->id_jadwal, $impor, $masuk, $pulang, $status, $masalah, $ringkasan);
        }

        // 3. hari terjadwal yang tidak ada di file sama sekali
        $hariIni = Carbon::today()->toDateString();
        foreach ($jadwal as $kunci => $j) {
            $tanggal = $j->tanggal_jadwal->toDateString();
            if (isset($diproses[$kunci]) || $tanggal > $hariIni) {
                continue;
            }
            if (Absensi::where('id_karyawan', $j->id_karyawan)->whereDate('tanggal_absen', $tanggal)->exists()) {
                continue;
            }
            $this->simpanAbsensi(
                $j->id_karyawan,
                $tanggal,
                $j->id_jadwal,
                $impor,
                null,
                null,
                AttendanceRules::PERLU_DICEK,
                ['Tidak ada di file Excel'],
                $ringkasan
            );
        }

        return $ringkasan;
    }

    /**
     * @param array<int,string> $masalah
     * @param array<string,mixed> $ringkasan
     */
    private function simpanAbsensi(
        string $idKaryawan,
        string $tanggal,
        ?string $idJadwal,
        ImporAbsensi $impor,
        ?string $masuk,
        ?string $pulang,
        string $status,
        array $masalah,
        array &$ringkasan
    ): void {
        $ada = Absensi::where('id_karyawan', $idKaryawan)->whereDate('tanggal_absen', $tanggal)->first();

        // jangan menimpa pekerjaan admin/karyawan: sudah ada konfirmasi atau kesalahan yang diselesaikan
        if ($ada && $this->terkunci($ada)) {
            $ringkasan['dilewati_sudah_dikonfirmasi']++;

            return;
        }

        $data = [
            'id_jadwal' => $idJadwal,
            'id_impor' => $impor->id_impor,
            'jam_masuk' => $masuk,
            'jam_pulang' => $pulang,
            'status_kehadiran' => $status,
            'keterangan_absen' => $masalah === [] ? null : implode('; ', $masalah),
        ];

        if ($ada) {
            $ada->update($data);
            $absensi = $ada;
            $ringkasan['absensi_diperbarui']++;
            KesalahanAbsensi::where('id_absensi', $absensi->id_absensi)
                ->where('status_perbaikan', 'Belum diperbaiki')
                ->delete();
        } else {
            $absensi = Absensi::create(['id_karyawan' => $idKaryawan, 'tanggal_absen' => $tanggal] + $data);
            $ringkasan['absensi_dibuat']++;
        }

        foreach ($masalah as $jenis) {
            KesalahanAbsensi::create([
                'id_absensi' => $absensi->id_absensi,
                'jenis_kesalahan' => $jenis,
                'status_perbaikan' => 'Belum diperbaiki',
            ]);
            $ringkasan['kesalahan_dicatat']++;
        }
    }

    private function terkunci(Absensi $absensi): bool
    {
        return $absensi->konfirmasi()->exists()
            || $absensi->kesalahan()->where('status_perbaikan', 'Selesai')->exists();
    }

    /**
     * @param array<int,Carbon> $scan terurut naik
     * @return array{0:?string,1:?string} [jam masuk, jam pulang]
     */
    private function tentukanMasukPulang(array $scan, ShiftKerja $shift): array
    {
        $pertama = $scan[0];
        $terakhir = $scan[count($scan) - 1];
        $jarakMenit = ($terakhir->timestamp - $pertama->timestamp) / 60;

        if (count($scan) >= 2 && $jarakMenit >= self::MIN_JARAK_SCAN_MENIT) {
            return [$pertama->format('H:i:s'), $terakhir->format('H:i:s')];
        }

        // satu scan saja: masuk atau pulang? lihat mana yang lebih dekat
        $detik = AttendanceRules::detik($pertama->format('H:i:s'));
        $keMulai = abs($detik - AttendanceRules::detik($shift->jam_mulai));
        $keSelesai = abs($detik - AttendanceRules::detik($shift->jam_selesai));

        return $keMulai <= $keSelesai
            ? [$pertama->format('H:i:s'), null]
            : [null, $pertama->format('H:i:s')];
    }

    /** Shift yang jam mulainya paling dekat dengan scan pertama (dipakai bila tidak ada jadwal). */
    private function tebakShift(Carbon $scanPertama, $semuaShift): ?ShiftKerja
    {
        $detik = AttendanceRules::detik($scanPertama->format('H:i:s'));

        return $semuaShift
            ->sortBy(fn (ShiftKerja $s) => abs($detik - AttendanceRules::detik($s->jam_mulai)))
            ->first();
    }

    // ------------------------------------------------------------------ pembacaan file

    /**
     * @return array<int,array{kunci:string,nama:string,shift:string,waktu:Carbon}>
     */
    private function bacaBaris(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        $data = $sheet->toArray(null, true, false, false);

        $barisHeader = null;
        $kolom = [];
        foreach (array_slice($data, 0, 15, true) as $i => $row) {
            $peta = [];
            foreach ($row as $idx => $sel) {
                $peta[$this->normHeader((string) $sel)] = $idx;
            }
            $nip = $this->cariKolom($peta, self::ALIAS_NIP);
            $nama = $this->cariKolom($peta, self::ALIAS_NAMA);
            $waktu = $this->cariKolom($peta, self::ALIAS_WAKTU);
            $tgl = $this->cariKolom($peta, self::ALIAS_TANGGAL);
            $jam = $this->cariKolom($peta, self::ALIAS_JAM);
            $shiftKol = $this->cariKolom($peta, self::ALIAS_SHIFT);
            $punyaWaktu = $waktu !== null || ($tgl !== null && $jam !== null);
            if (($nip !== null || $nama !== null) && $punyaWaktu) {
                $barisHeader = $i;
                $kolom = ['nip' => $nip, 'nama' => $nama, 'waktu' => $waktu, 'tgl' => $tgl, 'jam' => $jam, 'shift' => $shiftKol];
                break;
            }
        }

        if ($barisHeader === null) {
            throw new InvalidArgumentException(
                'Header tidak ditemukan. File harus punya kolom NIP/PIN, Nama, dan Waktu (atau Tanggal + Jam).'
            );
        }

        $hasil = [];
        foreach (array_slice($data, $barisHeader + 1) as $row) {
            $waktu = $this->bacaWaktu($row, $kolom);
            if ($waktu === null) {
                continue;
            }
            $hasil[] = [
                'kunci' => $kolom['nip'] !== null ? $this->teks($row[$kolom['nip']] ?? '') : '',
                'nama' => $kolom['nama'] !== null ? $this->teks($row[$kolom['nama']] ?? '') : '',
                'shift' => $kolom['shift'] !== null ? $this->teks($row[$kolom['shift']] ?? '') : '',
                'waktu' => $waktu,
            ];
        }

        return $hasil;
    }

    private function bacaWaktu(array $row, array $kolom): ?Carbon
    {
        if ($kolom['waktu'] !== null) {
            return $this->parseWaktu($row[$kolom['waktu']] ?? null);
        }

        $tanggal = $this->parseWaktu($row[$kolom['tgl']] ?? null);
        $jam = $row[$kolom['jam']] ?? null;
        if ($tanggal === null || $jam === null || $jam === '') {
            return null;
        }

        if (is_numeric($jam) && (float) $jam < 1) { // pecahan hari dari Excel
            $detik = (int) round((float) $jam * 86400);

            return $tanggal->copy()->startOfDay()->addSeconds($detik);
        }
        if ($jam instanceof DateTimeInterface) {
            return $tanggal->copy()->setTimeFromTimeString($jam->format('H:i:s'));
        }
        if (! preg_match('/^\d{1,2}[:.]\d{2}([:.]\d{2})?$/', trim((string) $jam))) {
            return null;
        }

        return $tanggal->copy()->setTimeFromTimeString(str_replace('.', ':', trim((string) $jam)));
    }

    private function parseWaktu(mixed $nilai): ?Carbon
    {
        if ($nilai === null || $nilai === '') {
            return null;
        }
        $tz = config('app.timezone');

        if ($nilai instanceof DateTimeInterface) {
            return Carbon::parse($nilai->format('Y-m-d H:i:s'), $tz);
        }
        if (is_numeric($nilai) && (float) $nilai > 20000) { // nomor seri tanggal Excel
            $dt = ExcelDate::excelToDateTimeObject((float) $nilai);

            return Carbon::parse($dt->format('Y-m-d H:i:s'), $tz);
        }

        $teks = trim((string) $nilai);
        $formatDicoba = [
            'd/m/Y H:i:s', 'd/m/Y H:i', 'd-m-Y H:i:s', 'd-m-Y H:i', 'Y-m-d H:i:s', 'Y-m-d H:i',
            '!d/m/Y', '!d-m-Y', '!Y-m-d',
        ];
        foreach ($formatDicoba as $format) {
            try {
                $dt = Carbon::createFromFormat($format, $teks, $tz);
            } catch (\Throwable) {
                continue;
            }
            $galat = \DateTimeImmutable::getLastErrors();
            if ($dt !== false && ($galat === false || ($galat['warning_count'] + $galat['error_count']) === 0)) {
                return $dt;
            }
        }

        return null;
    }

    private function cariKolom(array $peta, array $alias): ?int
    {
        foreach ($alias as $a) {
            if (array_key_exists($a, $peta)) {
                return $peta[$a];
            }
        }

        return null;
    }

    private function normHeader(string $teks): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim($teks)));
    }

    private function normNama(string $nama): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim($nama)));
    }

    /** NIP dibandingkan tanpa spasi/tanda baca dan tanpa nol di depan (Excel sering membuang nol). */
    private function normNip(string $nip): string
    {
        return ltrim(preg_replace('/[^0-9a-z]/i', '', mb_strtolower($nip)), '0');
    }

    private function teks(mixed $nilai): string
    {
        if (is_float($nilai) && floor($nilai) === $nilai) {
            return (string) (int) $nilai;
        }

        return trim((string) $nilai);
    }
}
