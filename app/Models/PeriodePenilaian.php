<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

class PeriodePenilaian extends BaseModel
{
    protected $table = 'periode_penilaian';
    protected $primaryKey = 'id_periode';
    protected $casts = ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date'];

    public function hasilKpi()
    {
        return $this->hasMany(HasilKpi::class, 'id_periode', 'id_periode');
    }

    /** Semester dihitung dari triwulan: 1-2 => 1, 3-4 => 2. */
    protected function semester(): Attribute
    {
        return Attribute::get(fn () => (int) ceil($this->triwulan / 2));
    }
}
