<?php

namespace App\Models;

class Karyawan extends BaseModel
{
    protected $table = 'karyawan';
    protected $primaryKey = 'id_karyawan';

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'id_dept', 'id_dept');
    }

    public function pengguna()
    {
        return $this->hasOne(Pengguna::class, 'id_karyawan', 'id_karyawan');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'id_karyawan', 'id_karyawan');
    }

    public function hasilKpi()
    {
        return $this->hasMany(HasilKpi::class, 'id_karyawan', 'id_karyawan');
    }
}
