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

class KpiAlurTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([IndikatorKpiSeeder::class, PredikatNilaiSeeder::class]);
    }

    public function test_alur_periode_hitung_periksa_validasi_dan_tampil_ke_karyawan(): void
    {
        $admin = $this->akun(Pengguna::ROLE_ADMIN, null, 'admin_uji');
        $dept = $this->dept('Formalin');
        $kepala = $this->karyawan($dept, 'Rudi Kepala');
        $akunKepala = $this->akun(Pengguna::ROLE_KEPALA_UNIT, $kepala);
        $andi = $this->karyawan($dept, 'Andi Pratama');
        $akunAndi = $this->akun(Pengguna::ROLE_KARYAWAN, $andi);
        $lain = $this->karyawan($this->dept('Resin'), 'Orang Resin');

        $impor = $this->impor($admin);
        $this->absensi($andi, $impor, '2026-10-05', '08:20:00', '17:00:00', 'Terlambat');
        $this->absensi($andi, $impor, '2026-10-06', null, null, 'Alpa');

        // 1. buat periode
        $this->actingAs($admin)->post(route('admin.kpi.periode.store'), ['tahun' => 2026, 'triwulan' => 4])
            ->assertRedirect(route('admin.kpi.periode.index'));
        $periode = PeriodePenilaian::where('tahun', 2026)->where('triwulan', 4)->firstOrFail();
        $this->assertSame('2026-10-01', $periode->tanggal_mulai->toDateString());
        $this->assertSame('2026-12-31', $periode->tanggal_selesai->toDateString());

        // periode kembar ditolak
        $this->post(route('admin.kpi.periode.store'), ['tahun' => 2026, 'triwulan' => 4])->assertSessionHasErrors('triwulan');

        // 2-3. pratinjau dan hitung
        $this->get(route('admin.kpi.periode.hitung', $periode))->assertOk()->assertSee('Andi Pratama');
        $this->post(route('admin.kpi.periode.hitung.proses', $periode))->assertRedirect();

        $hasil = HasilKpi::where('id_karyawan', $andi->id_karyawan)->firstOrFail();
        $this->assertLessThan(100, (float) $hasil->nilai_triwulan);
        $this->assertSame(100 - (int) $hasil->total_pengurang, (int) $hasil->nilai_triwulan);
        $this->assertSame('Belum diperiksa', $hasil->status_periksa);

        // 4. belum diperiksa kepala unit -> admin belum boleh validasi
        $this->post(route('admin.kpi.hasil.validasi', $hasil))->assertSessionHas('galat');
        $this->assertNotSame('Tervalidasi', $hasil->fresh()->status_validasi);

        // karyawan belum melihat nilai yang belum divalidasi
        $this->actingAs($akunAndi)->get(route('saya.kpi'))->assertOk()->assertSee('Belum ada nilai tervalidasi');

        // 5. kepala unit memeriksa
        $this->actingAs($akunKepala)->get(route('unit.kpi.pemeriksaan'))->assertOk()->assertSee('Andi Pratama');
        $this->post(route('unit.kpi.pemeriksaan.tandai', $hasil), ['catatan_periksa' => 'Sesuai'])->assertRedirect();
        $this->assertSame('Sudah diperiksa', $hasil->fresh()->status_periksa);

        // kepala unit tidak boleh memeriksa karyawan departemen lain
        $hasilLain = HasilKpi::where('id_karyawan', $lain->id_karyawan)->firstOrFail();
        $this->get(route('unit.kpi.pemeriksaan.show', $hasilLain))->assertNotFound();

        // 6. admin memvalidasi
        $this->actingAs($admin)->post(route('admin.kpi.hasil.validasi', $hasil))->assertRedirect();
        $this->assertSame('Tervalidasi', $hasil->fresh()->status_validasi);

        // 7. karyawan kini melihat nilainya
        $this->actingAs($akunAndi)->get(route('saya.kpi'))->assertOk();
        $this->get(route('saya.kpi.rincian'))->assertOk()->assertSee('Rincian Poin Pengurang');
        $this->get(route('saya.kpi.riwayat'))->assertOk();
    }

    public function test_hasil_kepala_unit_bisa_divalidasi_admin_tanpa_pemeriksaan(): void
    {
        $admin = $this->akun(Pengguna::ROLE_ADMIN, null, 'admin_uji');
        $dept = $this->dept();
        $kepala = $this->karyawan($dept, 'Rudi Kepala');
        $this->akun(Pengguna::ROLE_KEPALA_UNIT, $kepala);

        $this->actingAs($admin)->post(route('admin.kpi.periode.store'), ['tahun' => 2026, 'triwulan' => 4]);
        $periode = PeriodePenilaian::firstOrFail();
        $this->post(route('admin.kpi.periode.hitung.proses', $periode));

        $hasil = HasilKpi::where('id_karyawan', $kepala->id_karyawan)->firstOrFail();
        $this->post(route('admin.kpi.hasil.validasi', $hasil))->assertRedirect();
        $this->assertSame('Tervalidasi', $hasil->fresh()->status_validasi);
    }

    public function test_kembalikan_hasil_wajib_catatan(): void
    {
        $admin = $this->akun(Pengguna::ROLE_ADMIN, null, 'admin_uji');
        $this->karyawan($this->dept(), 'Andi Pratama');
        $this->actingAs($admin)->post(route('admin.kpi.periode.store'), ['tahun' => 2026, 'triwulan' => 4]);
        $this->post(route('admin.kpi.periode.hitung.proses', PeriodePenilaian::firstOrFail()));
        $hasil = HasilKpi::firstOrFail();

        $this->post(route('admin.kpi.hasil.kembalikan', $hasil), [])->assertSessionHasErrors('catatan_validasi');
        $this->post(route('admin.kpi.hasil.kembalikan', $hasil), ['catatan_validasi' => 'Hitung ulang'])->assertRedirect();
        $this->assertSame('Dikembalikan', $hasil->fresh()->status_validasi);
    }

    public function test_halaman_rekap_dan_laporan_terbuka_dan_ekspor_excel(): void
    {
        $admin = $this->akun(Pengguna::ROLE_ADMIN, null, 'admin_uji');
        $dept = $this->dept();
        $kepala = $this->karyawan($dept, 'Rudi Kepala');
        $akunKepala = $this->akun(Pengguna::ROLE_KEPALA_UNIT, $kepala);
        $this->karyawan($dept, 'Andi Pratama');
        $this->actingAs($admin)->post(route('admin.kpi.periode.store'), ['tahun' => 2026, 'triwulan' => 4]);

        $this->get(route('admin.kpi.rekap'))->assertOk();
        $this->get(route('admin.laporan'))->assertOk()->assertSee('Pratinjau Laporan');
        $this->get(route('admin.laporan.cetak'))->assertOk();
        $this->get(route('admin.kpi.rekap.excel'))->assertOk()->assertHeader('content-disposition');
        $this->get(route('admin.laporan.excel'))->assertOk()->assertHeader('content-disposition');

        $this->actingAs($akunKepala);
        foreach (['unit.kpi.nilai', 'unit.kpi.rekap', 'unit.laporan', 'unit.kpi.pemeriksaan'] as $rute) {
            $this->get(route($rute))->assertOk();
        }
        $this->get(route('unit.kpi.nilai.excel'))->assertOk();
    }

    public function test_kepala_unit_tidak_bisa_membuka_halaman_admin_kpi(): void
    {
        $kepala = $this->karyawan($this->dept());
        $this->actingAs($this->akun(Pengguna::ROLE_KEPALA_UNIT, $kepala));

        $this->get(route('admin.kpi.hasil.index'))->assertForbidden();
        $this->get(route('admin.laporan'))->assertForbidden();
    }
}
