<?php

namespace App\Models;

/**
 * Rencana lembur dengan alur tiga tahap:
 *   Kepala Unit mengajukan -> Kepala Produksi memeriksa -> Operasional Manager mengetahui -> General Manager memutuskan.
 * Setelah disetujui, kepala unit mengisi realisasi dan status menjadi "Selesai".
 */
class RencanaLembur extends BaseModel
{
    public const DRAF = 'Draf';
    public const MENUNGGU_PRODUKSI = 'Menunggu Ka. Produksi';
    public const MENUNGGU_OPERASIONAL = 'Menunggu Operasional Manager';
    public const MENUNGGU_GM = 'Menunggu General Manager';
    public const DISETUJUI = 'Disetujui';
    public const DITOLAK = 'Ditolak';
    public const DIKEMBALIKAN = 'Dikembalikan';
    public const SELESAI = 'Selesai';

    protected $table = 'rencana_lembur';
    protected $primaryKey = 'id_rencana';
    protected $casts = ['tanggal_lembur' => 'date', 'tanggal_pengajuan' => 'datetime'];

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'id_dept', 'id_dept');
    }

    public function pengaju()
    {
        return $this->belongsTo(Karyawan::class, 'id_pengaju', 'id_karyawan');
    }

    public function shift()
    {
        return $this->belongsTo(ShiftKerja::class, 'id_shift', 'id_shift');
    }

    public function detail()
    {
        return $this->hasMany(DetailLembur::class, 'id_rencana', 'id_rencana');
    }

    public function persetujuan()
    {
        return $this->hasMany(PersetujuanLembur::class, 'id_rencana', 'id_rencana');
    }

    /** Masih dalam rantai persetujuan. */
    public function dalamProses(): bool
    {
        return in_array($this->status_rencana, [self::MENUNGGU_PRODUKSI, self::MENUNGGU_OPERASIONAL, self::MENUNGGU_GM], true);
    }

    /** Disetujui GM (termasuk yang realisasinya sudah diisi). */
    public function disetujui(): bool
    {
        return in_array($this->status_rencana, [self::DISETUJUI, self::SELESAI], true);
    }

    public function bolehDiubah(): bool
    {
        return in_array($this->status_rencana, [self::DRAF, self::DIKEMBALIKAN], true);
    }

    public function totalJamRencana(): float
    {
        return (float) $this->detail->sum('jam_rencana');
    }

    /** Total jam realisasi, atau null bila realisasi belum diisi. */
    public function totalJamRealisasi(): ?float
    {
        if ($this->detail->isEmpty() || $this->detail->contains(fn ($d) => $d->total_jam === null)) {
            return null;
        }

        return (float) $this->detail->sum('total_jam');
    }

    /** Catatan terakhir dari petugas yang mengembalikan/menolak (untuk ditampilkan ke kepala unit). */
    public function catatanPenolakan(): ?string
    {
        return $this->persetujuan
            ->first(fn ($p) => in_array($p->status_persetujuan, ['Dikembalikan', 'Ditolak'], true))?->catatan;
    }

    /**
     * Empat langkah alur persetujuan untuk tampilan. Butuh relasi persetujuan.pengguna.karyawan dan pengaju.
     *
     * @return array<int,array{no:int,judul:string,petugas:string,ket:string,status:string}>
     */
    public function alur(): array
    {
        $p = $this->persetujuan->keyBy('tahap');
        $oleh = fn (?PersetujuanLembur $x) => $x ? (($x->pengguna?->namaTampil() ?? '—') . ' · ' . $x->tanggal_proses->translatedFormat('d M')) : '';

        $diajukan = $this->tanggal_pengajuan !== null;
        $langkah = [[
            'no' => 1, 'judul' => 'Diajukan', 'petugas' => 'Kepala Unit',
            'ket' => $diajukan ? (($this->pengaju?->nama_karyawan ?? '—') . ' · ' . $this->tanggal_pengajuan->translatedFormat('d M')) : 'Belum diajukan',
            'status' => $diajukan ? 'selesai' : 'aktif',
        ]];

        $langkah[] = $this->langkah(2, 'Diperiksa', 'Kepala Produksi', $p->get('periksa'), 'Diperiksa', self::MENUNGGU_PRODUKSI, $oleh);
        $langkah[] = $this->langkah(3, 'Diketahui', 'Operasional Manager', $p->get('ketahui'), 'Diketahui', self::MENUNGGU_OPERASIONAL, $oleh);
        $langkah[] = $this->langkah(4, 'Keputusan', 'General Manager', $p->get('putuskan'), null, self::MENUNGGU_GM, $oleh);

        return $langkah;
    }

    private function langkah(int $no, string $judul, string $petugas, ?PersetujuanLembur $rekam, ?string $statusOk, string $statusMenunggu, \Closure $oleh): array
    {
        if ($rekam) {
            $ok = $statusOk === null ? $rekam->status_persetujuan === 'Disetujui' : $rekam->status_persetujuan === $statusOk;
            $label = $statusOk === null || ! $ok ? $rekam->status_persetujuan . ' · ' : '';

            return ['no' => $no, 'judul' => $judul, 'petugas' => $petugas, 'ket' => $label . $oleh($rekam), 'status' => $ok ? 'selesai' : 'gagal'];
        }
        if ($this->status_rencana === $statusMenunggu) {
            return ['no' => $no, 'judul' => $judul, 'petugas' => $petugas, 'ket' => 'Menunggu ' . $petugas, 'status' => 'aktif'];
        }

        return ['no' => $no, 'judul' => $judul, 'petugas' => $petugas, 'ket' => $petugas, 'status' => 'menunggu'];
    }
}
