<?php

namespace App\Services;

use App\Domain\Attendance\AttendanceRules;
use App\Models\DetailLembur;
use App\Models\Karyawan;
use App\Models\Pengguna;
use App\Models\PersetujuanLembur;
use App\Models\RencanaLembur;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Alur rencana lembur. Setiap perpindahan status memeriksa status sebelumnya di dalam transaksi,
 * sehingga dua petugas yang menekan tombol bersamaan tidak bisa memproses pengajuan yang sama dua kali.
 *
 *   Draf/Dikembalikan --ajukan--> Menunggu Ka. Produksi --periksa--> Menunggu Operasional Manager
 *   --ketahui--> Menunggu General Manager --setujui--> Disetujui --isi realisasi--> Selesai
 *   (Ka. Produksi dapat mengembalikan -> Dikembalikan; General Manager dapat menolak -> Ditolak)
 *
 * Untuk membuat tahap "Operasional Manager" hanya pemberitahuan (tidak menahan persetujuan), ubah tujuan status
 * pada periksa() dari MENUNGGU_OPERASIONAL menjadi MENUNGGU_GM.
 */
class LemburService
{
    /** Lama lembur dalam jam (1 desimal). Jam selesai <= jam mulai dianggap melewati tengah malam. */
    public static function hitungJam(string $mulai, string $selesai): float
    {
        $detik = AttendanceRules::detik($selesai) - AttendanceRules::detik($mulai);
        if ($detik <= 0) {
            $detik += 86400;
        }

        return round($detik / 3600, 1);
    }

    public function nomorBaru(?int $tahun = null): string
    {
        $tahun ??= (int) now()->format('Y');
        $awalan = "LB-{$tahun}-";
        $terbesar = 0;
        foreach (RencanaLembur::where('no_pengajuan', 'like', $awalan . '%')->pluck('no_pengajuan') as $no) {
            $terbesar = max($terbesar, (int) substr($no, strlen($awalan)));
        }

        return $awalan . str_pad((string) ($terbesar + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Membuat atau memperbarui rencana lembur.
     *
     * @param array{tanggal_lembur:string,jam_mulai:string,jam_selesai:string,id_shift?:?string,uraian_pekerjaan:string,alasan_lembur:string} $data
     * @param array<int,array{id_karyawan:string,pekerjaan?:?string}> $baris
     */
    public function simpan(Karyawan $pengaju, array $data, array $baris, ?RencanaLembur $rencana = null, bool $ajukan = false): RencanaLembur
    {
        return DB::transaction(function () use ($pengaju, $data, $baris, $rencana, $ajukan) {
            $mulai = strlen($data['jam_mulai']) === 5 ? $data['jam_mulai'] . ':00' : $data['jam_mulai'];
            $selesai = strlen($data['jam_selesai']) === 5 ? $data['jam_selesai'] . ':00' : $data['jam_selesai'];
            $isi = [
                'tanggal_lembur' => $data['tanggal_lembur'],
                'jam_mulai' => $mulai,
                'jam_selesai' => $selesai,
                'id_shift' => $data['id_shift'] ?? null,
                'uraian_pekerjaan' => $data['uraian_pekerjaan'],
                'alasan_lembur' => $data['alasan_lembur'],
            ];

            if ($rencana) {
                $rencana = RencanaLembur::lockForUpdate()->findOrFail($rencana->id_rencana);
                if (! $rencana->bolehDiubah()) {
                    throw new DomainException('Pengajuan ini sedang diproses dan tidak dapat diubah.');
                }
                $rencana->update($isi);
                DetailLembur::where('id_rencana', $rencana->id_rencana)->delete();
            } else {
                $rencana = RencanaLembur::create($isi + [
                    'id_dept' => $pengaju->id_dept,
                    'id_pengaju' => $pengaju->id_karyawan,
                    'no_pengajuan' => $this->nomorBaru(),
                    'status_rencana' => RencanaLembur::DRAF,
                ]);
            }

            $jam = self::hitungJam($mulai, $selesai);
            $sudah = [];
            foreach ($baris as $b) {
                if (isset($sudah[$b['id_karyawan']])) {
                    continue;
                }
                $sudah[$b['id_karyawan']] = true;
                DetailLembur::create([
                    'id_rencana' => $rencana->id_rencana,
                    'id_karyawan' => $b['id_karyawan'],
                    'jam_rencana' => $jam,
                    'pekerjaan' => $b['pekerjaan'] ?? null,
                ]);
            }

            if ($ajukan) {
                $this->ajukan($rencana);
            }

            return $rencana->refresh();
        });
    }

    /** Draf atau pengajuan yang dikembalikan diajukan (ulang); riwayat persetujuan sebelumnya dibersihkan. */
    public function ajukan(RencanaLembur $rencana): RencanaLembur
    {
        return DB::transaction(function () use ($rencana) {
            $r = RencanaLembur::lockForUpdate()->findOrFail($rencana->id_rencana);
            if (! $r->bolehDiubah()) {
                throw new DomainException('Pengajuan ini sudah diajukan.');
            }
            if (! DetailLembur::where('id_rencana', $r->id_rencana)->exists()) {
                throw new DomainException('Tambahkan minimal satu karyawan sebelum mengajukan.');
            }

            PersetujuanLembur::where('id_rencana', $r->id_rencana)->delete();
            $r->update(['status_rencana' => RencanaLembur::MENUNGGU_PRODUKSI, 'tanggal_pengajuan' => now()]);

            return $r;
        });
    }

    public function periksa(RencanaLembur $rencana, Pengguna $pengguna, ?string $catatan = null): RencanaLembur
    {
        return $this->proses($rencana, 'periksa', 'Diperiksa', $pengguna, $catatan, RencanaLembur::MENUNGGU_PRODUKSI, RencanaLembur::MENUNGGU_OPERASIONAL);
    }

    public function kembalikan(RencanaLembur $rencana, Pengguna $pengguna, string $catatan): RencanaLembur
    {
        return $this->proses($rencana, 'periksa', 'Dikembalikan', $pengguna, $catatan, RencanaLembur::MENUNGGU_PRODUKSI, RencanaLembur::DIKEMBALIKAN);
    }

    public function ketahui(RencanaLembur $rencana, Pengguna $pengguna, ?string $catatan = null): RencanaLembur
    {
        return $this->proses($rencana, 'ketahui', 'Diketahui', $pengguna, $catatan, RencanaLembur::MENUNGGU_OPERASIONAL, RencanaLembur::MENUNGGU_GM);
    }

    public function setujui(RencanaLembur $rencana, Pengguna $pengguna, ?string $catatan = null): RencanaLembur
    {
        return $this->proses($rencana, 'putuskan', 'Disetujui', $pengguna, $catatan, RencanaLembur::MENUNGGU_GM, RencanaLembur::DISETUJUI);
    }

    public function tolak(RencanaLembur $rencana, Pengguna $pengguna, string $alasan, ?string $saran = null): RencanaLembur
    {
        $catatan = 'Alasan: ' . trim($alasan) . ($saran !== null && trim($saran) !== '' ? "\nSaran: " . trim($saran) : '');

        return $this->proses($rencana, 'putuskan', 'Ditolak', $pengguna, $catatan, RencanaLembur::MENUNGGU_GM, RencanaLembur::DITOLAK);
    }

    /**
     * Kepala unit mengisi jam lembur yang benar-benar dilaksanakan.
     *
     * @param array<string,array{jam_mulai_real:string,jam_selesai_real:string,catatan_realisasi?:?string}> $baris kunci = id_detail_lembur
     */
    public function isiRealisasi(RencanaLembur $rencana, Karyawan $pengisi, array $baris): RencanaLembur
    {
        return DB::transaction(function () use ($rencana, $pengisi, $baris) {
            $r = RencanaLembur::lockForUpdate()->findOrFail($rencana->id_rencana);
            if (! $r->disetujui()) {
                throw new DomainException('Realisasi hanya dapat diisi untuk rencana yang sudah disetujui.');
            }

            $detail = DetailLembur::where('id_rencana', $r->id_rencana)->get();
            foreach ($detail as $d) {
                $isi = $baris[$d->id_detail_lembur] ?? null;
                if ($isi === null) {
                    throw new DomainException('Jam realisasi semua karyawan harus diisi.');
                }
                $mulai = strlen($isi['jam_mulai_real']) === 5 ? $isi['jam_mulai_real'] . ':00' : $isi['jam_mulai_real'];
                $selesai = strlen($isi['jam_selesai_real']) === 5 ? $isi['jam_selesai_real'] . ':00' : $isi['jam_selesai_real'];
                $d->update([
                    'id_pengisi' => $pengisi->id_karyawan,
                    'jam_mulai_real' => $mulai,
                    'jam_selesai_real' => $selesai,
                    'total_jam' => self::hitungJam($mulai, $selesai),
                    'catatan_realisasi' => $isi['catatan_realisasi'] ?? null,
                    'tanggal_isi' => now(),
                ]);
            }
            $r->update(['status_rencana' => RencanaLembur::SELESAI]);

            return $r;
        });
    }

    private function proses(
        RencanaLembur $rencana,
        string $tahap,
        string $statusPersetujuan,
        Pengguna $pengguna,
        ?string $catatan,
        string $dari,
        string $ke
    ): RencanaLembur {
        return DB::transaction(function () use ($rencana, $tahap, $statusPersetujuan, $pengguna, $catatan, $dari, $ke) {
            $r = RencanaLembur::lockForUpdate()->findOrFail($rencana->id_rencana);
            if ($r->status_rencana !== $dari) {
                throw new DomainException('Status pengajuan sudah berubah. Muat ulang halaman untuk melihat kondisi terbaru.');
            }

            PersetujuanLembur::updateOrCreate(
                ['id_rencana' => $r->id_rencana, 'tahap' => $tahap],
                [
                    'id_pengguna' => $pengguna->id_pengguna,
                    'status_persetujuan' => $statusPersetujuan,
                    'catatan' => $catatan,
                    'tanggal_proses' => now(),
                ]
            );
            $r->update(['status_rencana' => $ke]);

            return $r;
        });
    }
}
