<?php

namespace App\Models;

class DetailLembur extends BaseModel
{
    protected $table = 'detail_lembur';
    protected $primaryKey = 'id_detail_lembur';
    protected $casts = ['tanggal_isi' => 'datetime'];

    public function rencana()
    {
        return $this->belongsTo(RencanaLembur::class, 'id_rencana', 'id_rencana');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    public function pengisi()
    {
        return $this->belongsTo(Karyawan::class, 'id_pengisi', 'id_karyawan');
    }
}
