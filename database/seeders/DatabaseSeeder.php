<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            IndikatorKpiSeeder::class,
            PredikatNilaiSeeder::class,
            ShiftKerjaSeeder::class,
            DepartemenSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
