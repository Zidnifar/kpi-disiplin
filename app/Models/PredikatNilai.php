<?php

namespace App\Models;

class PredikatNilai extends BaseModel
{
    protected $table = 'predikat_nilai';
    protected $primaryKey = 'id_predikat';
    protected $casts = ['nilai_min' => 'float', 'nilai_max' => 'float'];
}
