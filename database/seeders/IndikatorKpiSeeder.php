<?php

namespace Database\Seeders;

use App\Models\IndikatorKpi;
use Illuminate\Database\Seeder;

class IndikatorKpiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['A', 'Alpha', 5],
            ['ITMTP', 'Izin Tidak Masuk Bekerja Tanpa Prosedur', 2],
            ['PKK3', 'Pelanggaran Kebijakan K3', 3],
            ['SP1', 'Surat Peringatan 1', 10],
            ['SP2', 'Surat Peringatan 2', 15],
            ['SP3', 'Surat Peringatan 3', 20],
            ['SPPT', 'Surat Peringatan Pertama Terakhir', 25],
            ['ST', 'Surat Teguran', 5],
            ['STSK', 'Sakit Tanpa Surat Keterangan Dokter', 2],
            ['TMK', 'Terlambat Masuk Kerja Tanpa Keterangan', 1],
        ];

        foreach ($data as [$kode, $nama, $bobot]) {
            IndikatorKpi::updateOrCreate(
                ['kode_indikator' => $kode],
                ['nama_indikator' => $nama, 'bobot_poin' => $bobot, 'status_indikator' => 'Aktif']
            );
        }
    }
}
