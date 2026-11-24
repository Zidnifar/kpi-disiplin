<?php

namespace App\Models;

class HasilKpi extends BaseModel
{
    protected $table = 'hasil_kpi';
    protected $primaryKey = 'id_hasil';
    protected $casts = [
        'nilai_triwulan' => 'float',
        'tanggal_penilaian' => 'datetime',
        'tanggal_periksa' => 'datetime',
        'tanggal_validasi' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    public function periode()
    {
        return $this->belongsTo(PeriodePenilaian::class, 'id_periode', 'id_periode');
    }

    public function predikat()
    {
        return $this->belongsTo(PredikatNilai::class, 'id_predikat', 'id_predikat');
    }

    public function detail()
    {
        return $this->hasMany(DetailPenilaianKpi::class, 'id_hasil', 'id_hasil');
    }

    public function pemeriksa()
    {
        return $this->belongsTo(Pengguna::class, 'id_pemeriksa', 'id_pengguna');
    }

    public function pemvalidasi()
    {
        return $this->belongsTo(Pengguna::class, 'id_pemvalidasi', 'id_pengguna');
    }
}
