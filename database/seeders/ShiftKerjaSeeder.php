<?php

namespace Database\Seeders;

use App\Models\ShiftKerja;
use Illuminate\Database\Seeder;

class ShiftKerjaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['P', 'Pagi', '08:00:00', '17:00:00'],
            ['M', 'Malam', '17:00:00', '20:00:00'],
        ];

        foreach ($data as [$kode, $nama, $mulai, $selesai]) {
            ShiftKerja::updateOrCreate(
                ['kode_shift' => $kode],
                [
                    'nama_shift' => $nama,
                    'jam_mulai' => $mulai,
                    'jam_selesai' => $selesai,
                    'toleransi_menit' => 0,
                    'batas_pulang_awal_menit' => 60,
                ]
            );
        }
    }
}
