<?php

namespace App\Models;

class ImporAbsensi extends BaseModel
{
    protected $table = 'impor_absensi';
    protected $primaryKey = 'id_impor';
    protected $casts = ['tanggal_impor' => 'datetime'];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'id_impor', 'id_impor');
    }
}
