<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ganti password lewat .env (ADMIN_PASSWORD) sebelum dipakai di server sungguhan.
        Pengguna::updateOrCreate(
            ['username' => 'admin'],
            [
                'id_karyawan' => null,
                'password' => env('ADMIN_PASSWORD', 'ganti-password-ini'),
                'role' => Pengguna::ROLE_ADMIN,
                'status_akun' => 'Aktif',
            ]
        );
    }
}
