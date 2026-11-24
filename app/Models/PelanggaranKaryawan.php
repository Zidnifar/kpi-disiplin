<?php

namespace App\Models;

class PelanggaranKaryawan extends BaseModel
{
    protected $table = 'pelanggaran_karyawan';
    protected $primaryKey = 'id_pelanggaran';
    protected $casts = ['tanggal_pelanggaran' => 'date'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    public function indikator()
    {
        return $this->belongsTo(IndikatorKpi::class, 'id_indikator', 'id_indikator');
    }

    public function absensi()
    {
        return $this->belongsTo(Absensi::class, 'id_absensi', 'id_absensi');
    }
}
