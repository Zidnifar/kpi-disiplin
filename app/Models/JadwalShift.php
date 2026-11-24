<?php

namespace App\Models;

class JadwalShift extends BaseModel
{
    protected $table = 'jadwal_shift';
    protected $primaryKey = 'id_jadwal';
    protected $casts = ['tanggal_jadwal' => 'date'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    public function shift()
    {
        return $this->belongsTo(ShiftKerja::class, 'id_shift', 'id_shift');
    }
}
