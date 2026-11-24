<?php

namespace Database\Seeders;

use App\Models\PredikatNilai;
use Illuminate\Database\Seeder;

class PredikatNilaiSeeder extends Seeder
{
    public function run(): void
    {
        // Rentang kontinu agar nilai desimal (mis. 89,5) tidak jatuh di celah.
        $data = [
            ['Sangat Baik', 90.00, 100.00],
            ['Baik', 80.00, 89.99],
            ['Cukup', 70.00, 79.99],
            ['Kurang', 0.00, 69.99],
        ];

        foreach ($data as [$nama, $min, $max]) {
            PredikatNilai::updateOrCreate(
                ['nama_predikat' => $nama],
                ['nilai_min' => $min, 'nilai_max' => $max, 'status_predikat' => 'Aktif']
            );
        }
    }
}
