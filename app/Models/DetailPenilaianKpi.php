<?php

namespace App\Models;

class DetailPenilaianKpi extends BaseModel
{
    protected $table = 'detail_penilaian_kpi';
    protected $primaryKey = 'id_detail';

    public function hasil()
    {
        return $this->belongsTo(HasilKpi::class, 'id_hasil', 'id_hasil');
    }

    public function indikator()
    {
        return $this->belongsTo(IndikatorKpi::class, 'id_indikator', 'id_indikator');
    }
}
