<?php

namespace Tests\Feature;

use App\Models\Departemen;
use App\Models\DetailLembur;
use App\Models\Karyawan;
use App\Models\PersetujuanLembur;
use App\Models\Pengguna;
use App\Models\RencanaLembur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class LemburAlurTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private Departemen $dept;
    private Karyawan $andi;
    private Pengguna $kepalaUnit;
    private Pengguna $akunAndi;
    private Pengguna $produksi;
    private Pengguna $operasional;
    private Pengguna $gm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dept = $this->dept('Formalin');
        $this->kepalaUnit = $this->akun(Pengguna::ROLE_KEPALA_UNIT, $this->karyawan($this->dept, 'Rudi Kepala'));
        $this->andi = $this->karyawan($this->dept, 'Andi Pratama');
        $this->akunAndi = $this->akun(Pengguna::ROLE_KARYAWAN, $this->andi);
        $manajemen = $this->dept('Operation');
        $this->produksi = $this->akun(Pengguna::ROLE_KEPALA_PRODUKSI, $this->karyawan($manajemen, 'Sari Produksi'));
        $this->operasional = $this->akun(Pengguna::ROLE_OPERASIONAL_MANAGER, $this->karyawan($manajemen, 'Maya Operasional'));
        $this->gm = $this->akun(Pengguna::ROLE_GENERAL_MANAGER, $this->karyawan($manajemen, 'Hadi GM'));
    }

    /** @return array<string,mixed> */
    private function isian(array $ubah = []): array
    {
        return array_merge([
            'tanggal_lembur' => now()->addDays(2)->toDateString(),
            'jam_mulai' => '17:00',
            'jam_selesai' => '20:00',
            'uraian_pekerjaan' => 'Pengisian tangki formalin',
            'alasan_lembur' => 'Target produksi akhir bulan',
            'karyawan' => [['id_karyawan' => $this->andi->id_karyawan, 'pekerjaan' => 'Operator tangki']],
            'aksi' => 'ajukan',
        ], $ubah);
    }

    private function ajukan(): RencanaLembur
    {
        $this->actingAs($this->kepalaUnit)->post(route('unit.lembur.store'), $this->isian())->assertRedirect();

        return RencanaLembur::firstOrFail();
    }

    public function test_alur_lengkap_sampai_realisasi(): void
    {
        $rencana = $this->ajukan();
        $this->assertSame('Menunggu Ka. Produksi', $rencana->status_rencana);
        $this->assertSame('LB-' . now()->format('Y') . '-001', $rencana->no_pengajuan);
        $this->assertSame(3.0, (float) $rencana->detail()->firstOrFail()->jam_rencana);

        // General Manager belum bisa memutuskan sebelum diperiksa dan diketahui
        $this->actingAs($this->gm)->get(route('lembur.putuskan.show', $rencana))->assertNotFound();
        $this->post(route('lembur.putuskan.setujui', $rencana))->assertSessionHas('galat');
        $this->assertSame('Menunggu Ka. Produksi', $rencana->fresh()->status_rencana);

        // tahap 1: Kepala Produksi
        $this->actingAs($this->produksi)->get(route('lembur.periksa.index'))->assertOk()->assertSee($rencana->no_pengajuan);
        $this->post(route('lembur.periksa.periksa', $rencana), ['catatan' => 'Layak'])->assertRedirect();
        $this->assertSame('Menunggu Operasional Manager', $rencana->fresh()->status_rencana);

        // tahap 2: Operasional Manager
        $this->actingAs($this->operasional)->get(route('lembur.ketahui.show', $rencana))->assertOk();
        $this->post(route('lembur.ketahui.tandai', $rencana))->assertRedirect();
        $this->assertSame('Menunggu General Manager', $rencana->fresh()->status_rencana);

        // tahap 3: General Manager
        $this->actingAs($this->gm)->get(route('lembur.putuskan.show', $rencana))->assertOk();
        $this->post(route('lembur.putuskan.setujui', $rencana))->assertRedirect();
        $this->assertSame('Disetujui', $rencana->fresh()->status_rencana);

        // realisasi oleh kepala unit
        $detail = DetailLembur::firstOrFail();
        $this->actingAs($this->kepalaUnit)->get(route('unit.realisasi.edit', $rencana))->assertOk();
        $this->put(route('unit.realisasi.update', $rencana), [
            'baris' => [$detail->id_detail_lembur => ['jam_mulai_real' => '17:00', 'jam_selesai_real' => '20:30', 'catatan_realisasi' => 'Molor']],
        ])->assertRedirect(route('unit.realisasi.index'));

        $this->assertSame('Selesai', $rencana->fresh()->status_rencana);
        $this->assertSame(3.5, (float) $detail->fresh()->total_jam);

        // karyawan melihat status lembur miliknya
        $this->actingAs($this->akunAndi)->get(route('saya.lembur'))->assertOk()->assertSee($rencana->no_pengajuan);
        $this->get(route('saya.lembur.show', $rencana))->assertOk();

        // rekap admin
        $admin = $this->akun(Pengguna::ROLE_ADMIN, null, 'admin_uji');
        $this->actingAs($admin)->get(route('admin.lembur.index'))->assertOk()->assertSee($rencana->no_pengajuan);
        $this->get(route('admin.lembur.excel'))->assertOk()->assertHeader('content-disposition');
    }

    public function test_pemeriksaan_tidak_bisa_diproses_dua_kali(): void
    {
        $rencana = $this->ajukan();
        $this->actingAs($this->produksi)->post(route('lembur.periksa.periksa', $rencana));
        $this->post(route('lembur.periksa.periksa', $rencana))->assertSessionHas('galat');

        $this->assertSame(1, PersetujuanLembur::where('id_rencana', $rencana->id_rencana)->count());
        $this->assertSame('Menunggu Operasional Manager', $rencana->fresh()->status_rencana);
    }

    public function test_dikembalikan_lalu_diajukan_ulang_membersihkan_riwayat(): void
    {
        $rencana = $this->ajukan();

        $this->actingAs($this->produksi)->post(route('lembur.periksa.kembalikan', $rencana), [])->assertSessionHasErrors('catatan');
        $this->post(route('lembur.periksa.kembalikan', $rencana), ['catatan' => 'Jam terlalu panjang'])->assertRedirect();
        $this->assertSame('Dikembalikan', $rencana->fresh()->status_rencana);

        $this->actingAs($this->kepalaUnit)->get(route('unit.lembur.edit', $rencana))->assertOk()->assertSee('Jam terlalu panjang');
        $this->put(route('unit.lembur.update', $rencana), $this->isian(['jam_selesai' => '19:00']))->assertRedirect();

        $rencana->refresh();
        $this->assertSame('Menunggu Ka. Produksi', $rencana->status_rencana);
        $this->assertSame(0, PersetujuanLembur::where('id_rencana', $rencana->id_rencana)->count());
        $this->assertSame(2.0, (float) $rencana->detail()->firstOrFail()->jam_rencana);
    }

    public function test_general_manager_menolak_wajib_alasan(): void
    {
        $rencana = $this->ajukan();
        $this->actingAs($this->produksi)->post(route('lembur.periksa.periksa', $rencana));
        $this->actingAs($this->operasional)->post(route('lembur.ketahui.tandai', $rencana));

        $this->actingAs($this->gm)->post(route('lembur.putuskan.tolak.kirim', $rencana), [])->assertSessionHasErrors('alasan_penolakan');
        $this->post(route('lembur.putuskan.tolak.kirim', $rencana), ['alasan_penolakan' => 'Anggaran habis', 'saran' => 'Ajukan bulan depan'])->assertRedirect();

        $rencana->refresh();
        $this->assertSame('Ditolak', $rencana->status_rencana);
        $this->assertStringContainsString('Anggaran habis', (string) PersetujuanLembur::where('tahap', 'putuskan')->value('catatan'));

        // pengajuan yang ditolak tidak bisa diubah lagi
        $this->actingAs($this->kepalaUnit)->get(route('unit.lembur.edit', $rencana))->assertRedirect(route('unit.lembur.show', $rencana));
    }

    public function test_validasi_form_pengajuan(): void
    {
        $this->actingAs($this->kepalaUnit);

        $this->post(route('unit.lembur.store'), $this->isian(['tanggal_lembur' => now()->subDay()->toDateString()]))
            ->assertSessionHasErrors('tanggal_lembur');

        $dept2 = $this->dept('Resin');
        $orangLain = $this->karyawan($dept2, 'Orang Resin');
        $this->post(route('unit.lembur.store'), $this->isian(['karyawan' => [['id_karyawan' => $orangLain->id_karyawan]]]))
            ->assertSessionHasErrors('karyawan.0.id_karyawan');

        $this->post(route('unit.lembur.store'), $this->isian(['karyawan' => []]))->assertSessionHasErrors('karyawan');
        $this->assertSame(0, RencanaLembur::count());
    }

    public function test_simpan_draf_tidak_masuk_antrean_produksi(): void
    {
        $this->actingAs($this->kepalaUnit)->post(route('unit.lembur.store'), $this->isian(['aksi' => 'draf']))->assertRedirect();
        $rencana = RencanaLembur::firstOrFail();
        $this->assertSame('Draf', $rencana->status_rencana);

        $this->actingAs($this->produksi)->get(route('lembur.periksa.show', $rencana))->assertNotFound();
    }

    public function test_batas_akses_antar_peran_dan_antar_unit(): void
    {
        $rencana = $this->ajukan();

        // karyawan biasa tidak boleh membuka halaman kepala unit / persetujuan
        $this->actingAs($this->akunAndi)->get(route('unit.lembur.index'))->assertForbidden();
        $this->get(route('lembur.periksa.index'))->assertForbidden();
        $this->actingAs($this->operasional)->get(route('lembur.periksa.index'))->assertForbidden();

        // kepala unit dari departemen lain tidak boleh melihat pengajuan unit ini
        $unitLain = $this->akun(Pengguna::ROLE_KEPALA_UNIT, $this->karyawan($this->dept('Resin'), 'Kepala Resin'));
        $this->actingAs($unitLain)->get(route('unit.lembur.show', $rencana))->assertNotFound();

        // karyawan yang tidak terlibat tidak boleh melihat detail lembur
        $orangLain = $this->akun(Pengguna::ROLE_KARYAWAN, $this->karyawan($this->dept('Resin'), 'Bukan Peserta'));
        $this->actingAs($orangLain)->get(route('saya.lembur.show', $rencana))->assertNotFound();
    }
}
