<?php

namespace Database\Seeders;

use App\Models\Departemen;
use Illuminate\Database\Seeder;

class DepartemenSeeder extends Seeder
{
    public function run(): void
    {
        // Nama departemen mengikuti contoh di mockup; sesuaikan dengan struktur perusahaan sebenarnya.
        foreach (['Operation', 'Formalin', 'Resin', 'Laboratorium', 'Marketing', 'Administrasi'] as $nama) {
            Departemen::firstOrCreate(['nama_dept' => $nama]);
        }
    }
}
