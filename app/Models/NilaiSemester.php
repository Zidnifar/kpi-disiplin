<?php

namespace App\Models;

class NilaiSemester extends BaseModel
{
    protected $table = 'nilai_semester';
    protected $primaryKey = 'id_nilai_semester';
    protected $casts = ['nilai_semester' => 'float', 'tanggal_hitung' => 'datetime'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    public function predikat()
    {
        return $this->belongsTo(PredikatNilai::class, 'id_predikat', 'id_predikat');
    }

    public function hasilTwPertama()
    {
        return $this->belongsTo(HasilKpi::class, 'id_hasil_tw_pertama', 'id_hasil');
    }

    public function hasilTwKedua()
    {
        return $this->belongsTo(HasilKpi::class, 'id_hasil_tw_kedua', 'id_hasil');
    }
}
