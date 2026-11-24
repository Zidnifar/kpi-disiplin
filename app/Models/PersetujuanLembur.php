<?php

namespace App\Models;

class PersetujuanLembur extends BaseModel
{
    protected $table = 'persetujuan_lembur';
    protected $primaryKey = 'id_persetujuan';
    protected $casts = ['tanggal_proses' => 'datetime'];

    public function rencana()
    {
        return $this->belongsTo(RencanaLembur::class, 'id_rencana', 'id_rencana');
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}
