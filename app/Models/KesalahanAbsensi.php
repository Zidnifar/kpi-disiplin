<?php

namespace App\Models;

class KesalahanAbsensi extends BaseModel
{
    protected $table = 'kesalahan_absensi';
    protected $primaryKey = 'id_kesalahan';
    protected $casts = ['tanggal_perbaikan' => 'datetime'];

    public function absensi()
    {
        return $this->belongsTo(Absensi::class, 'id_absensi', 'id_absensi');
    }
}
