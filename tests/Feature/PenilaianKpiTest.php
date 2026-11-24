<?php

namespace Tests\Feature;

use App\Models\HasilKpi;
use App\Models\PeriodePenilaian;
use App\Models\Pengguna;
use Database\Seeders\IndikatorKpiSeeder;
use Database\Seeders\PredikatNilaiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class PenilaianKpiTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([IndikatorKpiSeeder::class, PredikatNilaiSeeder::class]);
    }

    public function test_halaman_penilaian_tanpa_periode_menunjukkan_petunjuk(): void
    {
        $this->actingAs($this->akun(Pengguna::ROLE_ADMIN, null, 'admin_uji'))
            ->get(route('admin.kpi.penilaian.index'))->assertOk()->assertSee('Belum ada periode penilaian');
    }

    public function test_penilaian_menampilkan_kesiapan_bulan_dan_menjalankan_perhitungan(): void
    {
        $admin = $this->akun(Pengguna::ROLE_ADMIN, null, 'admin_uji');
        $andi = $this->karyawan($this->dept(), 'Andi Pratama');
        $impor = $this->impor($admin);
        $this->absensi($andi, $impor, '2026-10-05', '08:20:00', '17:00:00', 'Terlambat');
        $kesalahan = $this->absensi($andi, $impor, '2026-11-06', null, null, 'Perlu Dicek');
        $this->kesalahan($kesalahan);

        $this->actingAs($admin)->post(route('admin.kpi.periode.store'), ['tahun' => 2026, 'triwulan' => 4]);
        $periode = PeriodePenilaian::firstOrFail();

        $this->get(route('admin.kpi.penilaian.index', ['periode' => $periode->id_periode]))
            ->assertOk()
            ->assertSee('Oktober')->assertSee('November')->assertSee('Desember')
            ->assertSee('1 data belum dikonfirmasi')
            ->assertSee('Andi Pratama');

        // ada data belum dikonfirmasi -> ditolak kecuali dipaksa
        $this->post(route('admin.kpi.penilaian.hitung'), ['periode' => $periode->id_periode])->assertSessionHas('galat');
        $this->assertSame(0, HasilKpi::count());

        $this->post(route('admin.kpi.penilaian.hitung'), ['periode' => $periode->id_periode, 'paksa' => 1])->assertSessionHas('sukses');
        $this->assertSame(1, HasilKpi::count());
    }
}