<?php

namespace App\Models;

class Departemen extends BaseModel
{
    protected $table = 'departemen';
    protected $primaryKey = 'id_dept';

    public function karyawan()
    {
        return $this->hasMany(Karyawan::class, 'id_dept', 'id_dept');
    }

    public function kepalaUnit()
    {
        return $this->belongsTo(Karyawan::class, 'id_kepala_unit', 'id_karyawan');
    }
}
