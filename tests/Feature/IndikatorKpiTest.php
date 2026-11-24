<?php

namespace Tests\Feature;

use App\Models\HasilKpi;
use App\Models\IndikatorKpi;
use App\Models\PelanggaranKaryawan;
use App\Models\PeriodePenilaian;
use App\Models\Pengguna;
use Database\Seeders\IndikatorKpiSeeder;
use Database\Seeders\PredikatNilaiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class IndikatorKpiTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([IndikatorKpiSeeder::class, PredikatNilaiSeeder::class]);
        $this->actingAs($this->akun(Pengguna::ROLE_ADMIN, null, 'admin_uji'));
    }

    private function kriteria(string $kode): IndikatorKpi
    {
        return IndikatorKpi::where('kode_indikator', $kode)->firstOrFail();
    }

    public function test_daftar_dan_tambah_kriteria(): void
    {
        $this->get(route('admin.kpi.indikator.index'))->assertOk()->assertSee('Alpha');
        $this->get(route('admin.kpi.indikator.create'))->assertOk();

        $this->post(route('admin.kpi.indikator.store'), [
            'kode_indikator' => 'zz1', 'nama_indikator' => 'Kriteria Uji', 'bobot_poin' => 4, 'status_indikator' => 'Aktif',
        ])->assertRedirect(route('admin.kpi.indikator.index'));

        $this->assertSame(4, $this->kriteria('ZZ1')->bobot_poin);
    }

    public function test_kode_kembar_dan_bobot_tidak_valid_ditolak(): void
    {
        $this->post(route('admin.kpi.indikator.store'), [
            'kode_indikator' => 'tmk', 'nama_indikator' => 'Kembar', 'bobot_poin' => 1, 'status_indikator' => 'Aktif',
        ])->assertSessionHasErrors('kode_indikator');

        $this->post(route('admin.kpi.indikator.store'), [
            'kode_indikator' => 'ZZ2', 'nama_indikator' => 'Bobot besar', 'bobot_poin' => 500, 'status_indikator' => 'Aktif',
        ])->assertSessionHasErrors('bobot_poin');
    }

    public function test_ubah_bobot_kriteria(): void
    {
        $alpha = $this->kriteria('A');

        $this->get(route('admin.kpi.indikator.edit', $alpha))->assertOk();
        $this->put(route('admin.kpi.indikator.update', $alpha), [
            'kode_indikator' => 'A', 'nama_indikator' => 'Alpha', 'bobot_poin' => 7, 'status_indikator' => 'Aktif',
        ])->assertRedirect(route('admin.kpi.indikator.index'));

        $this->assertSame(7, $alpha->fresh()->bobot_poin);
    }

    public function test_kriteria_bawaan_aturan_absensi_terlindungi(): void
    {
        $tmk = $this->kriteria('TMK');

        // kode dan status dikunci, bobot tetap boleh diubah
        $this->put(route('admin.kpi.indikator.update', $tmk), [
            'kode_indikator' => 'XYZ', 'nama_indikator' => 'Terlambat', 'bobot_poin' => 2, 'status_indikator' => 'Nonaktif',
        ])->assertRedirect();
        $tmk->refresh();
        $this->assertSame('TMK', $tmk->kode_indikator);
        $this->assertSame('Aktif', $tmk->status_indikator);
        $this->assertSame(2, $tmk->bobot_poin);

        $this->delete(route('admin.kpi.indikator.destroy', $tmk))->assertSessionHas('galat');
        $this->assertNotNull(IndikatorKpi::find($tmk->id_indikator));
    }

    public function test_hapus_kriteria_tidak_terpakai_berhasil_dan_yang_terpakai_ditolak(): void
    {
        $bebas = IndikatorKpi::create(['kode_indikator' => 'ZZ9', 'nama_indikator' => 'Bebas', 'bobot_poin' => 1, 'status_indikator' => 'Aktif']);
        $this->delete(route('admin.kpi.indikator.destroy', $bebas))->assertRedirect(route('admin.kpi.indikator.index'));
        $this->assertNull(IndikatorKpi::find($bebas->id_indikator));

        $terpakai = IndikatorKpi::create(['kode_indikator' => 'ZZ8', 'nama_indikator' => 'Terpakai', 'bobot_poin' => 1, 'status_indikator' => 'Aktif']);
        $andi = $this->karyawan($this->dept(), 'Andi Pratama');
        PelanggaranKaryawan::create([
            'id_karyawan' => $andi->id_karyawan, 'id_indikator' => $terpakai->id_indikator,
            'tanggal_pelanggaran' => '2026-11-10', 'sumber' => 'Manual',
        ]);

        $this->delete(route('admin.kpi.indikator.destroy', $terpakai))->assertSessionHas('galat');
        $this->assertNotNull(IndikatorKpi::find($terpakai->id_indikator));
    }

    public function test_kriteria_nonaktif_tidak_ikut_dihitung(): void
    {
        $k3 = IndikatorKpi::create(['kode_indikator' => 'K3X', 'nama_indikator' => 'Uji K3', 'bobot_poin' => 10, 'status_indikator' => 'Aktif']);
        $andi = $this->karyawan($this->dept(), 'Andi Pratama');
        PelanggaranKaryawan::create([
            'id_karyawan' => $andi->id_karyawan, 'id_indikator' => $k3->id_indikator,
            'tanggal_pelanggaran' => '2026-11-10', 'sumber' => 'K3',
        ]);

        $this->post(route('admin.kpi.periode.store'), ['tahun' => 2026, 'triwulan' => 4]);
        $periode = PeriodePenilaian::firstOrFail();

        $this->post(route('admin.kpi.periode.hitung.proses', $periode));
        $this->assertSame(90.0, (float) HasilKpi::firstOrFail()->nilai_triwulan);

        $this->put(route('admin.kpi.indikator.update', $k3), [
            'kode_indikator' => 'K3X', 'nama_indikator' => 'Uji K3', 'bobot_poin' => 10, 'status_indikator' => 'Nonaktif',
        ])->assertRedirect();

        $this->post(route('admin.kpi.periode.hitung.proses', $periode));
        $this->assertSame(100.0, (float) HasilKpi::firstOrFail()->nilai_triwulan);
    }
}