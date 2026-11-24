<?php

namespace App\Models;

class KonfirmasiAbsensi extends BaseModel
{
    protected $table = 'konfirmasi_absensi';
    protected $primaryKey = 'id_konfir';
    protected $casts = ['tanggal_konfir' => 'datetime', 'tanggal_verifikasi' => 'datetime'];

    public function absensi()
    {
        return $this->belongsTo(Absensi::class, 'id_absensi', 'id_absensi');
    }

    public function pemverifikasi()
    {
        return $this->belongsTo(Pengguna::class, 'id_pemverifikasi', 'id_pengguna');
    }

    /** Ringkasan jawaban karyawan untuk tabel: "Data sudah benar" atau "Koreksi jam masuk 07:05, jam pulang 15:00". */
    public function ringkasanJawaban(): string
    {
        if ($this->jawaban_konfir !== 'Koreksi') {
            return 'Data sudah benar';
        }

        $bagian = [];
        if ($this->jam_masuk_koreksi) {
            $bagian[] = 'jam masuk ' . substr($this->jam_masuk_koreksi, 0, 5);
        }
        if ($this->jam_pulang_koreksi) {
            $bagian[] = 'jam pulang ' . substr($this->jam_pulang_koreksi, 0, 5);
        }

        return 'Koreksi ' . ($bagian === [] ? 'data' : implode(', ', $bagian));
    }
}
