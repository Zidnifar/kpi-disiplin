<?php

namespace App\Models;

class Absensi extends BaseModel
{
    protected $table = 'absensi';
    protected $primaryKey = 'id_absensi';
    protected $casts = ['tanggal_absen' => 'date'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalShift::class, 'id_jadwal', 'id_jadwal');
    }

    public function impor()
    {
        return $this->belongsTo(ImporAbsensi::class, 'id_impor', 'id_impor');
    }

    public function kesalahan()
    {
        return $this->hasMany(KesalahanAbsensi::class, 'id_absensi', 'id_absensi');
    }

    public function konfirmasi()
    {
        return $this->hasOne(KonfirmasiAbsensi::class, 'id_absensi', 'id_absensi');
    }
}
