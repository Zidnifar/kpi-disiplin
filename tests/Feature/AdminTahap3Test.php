<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\JadwalShift;
use App\Models\KesalahanAbsensi;
use App\Models\KonfirmasiAbsensi;
use App\Models\Karyawan;
use App\Models\Pengguna;
use App\Models\ShiftKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class AdminTahap3Test extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function admin(): Pengguna
    {
        return $this->akun(Pengguna::ROLE_ADMIN, null, 'admin_uji');
    }

    public function test_halaman_admin_tahap_3_terbuka(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        foreach (['admin.pengguna.index', 'admin.pengguna.create', 'admin.karyawan.index', 'admin.karyawan.create',
            'admin.shift.index', 'admin.shift.create', 'admin.absensi.index', 'admin.absensi.impor',
            'admin.kesalahan.index', 'admin.verifikasi.index'] as $rute) {
            $this->get(route($rute))->assertOk();
        }
    }

    public function test_peran_lain_tidak_bisa_membuka_halaman_admin(): void
    {
        $k = $this->karyawan($this->dept());
        $this->actingAs($this->akun(Pengguna::ROLE_KARYAWAN, $k));

        $this->get(route('admin.pengguna.index'))->assertForbidden();
        $this->get(route('admin.absensi.index'))->assertForbidden();
        $this->post(route('admin.absensi.proses'), [])->assertForbidden();
    }

    public function test_tambah_karyawan_dan_pengguna(): void
    {
        $this->actingAs($this->admin());
        $dept = $this->dept();

        $this->post(route('admin.karyawan.store'), [
            'nip' => '12345', 'nama_karyawan' => 'Budi Santoso', 'jabatan' => 'Operator',
            'id_dept' => $dept->id_dept, 'status_karyawan' => 'Aktif',
        ])->assertRedirect(route('admin.karyawan.index'));
        $karyawan = Karyawan::where('nip', '12345')->firstOrFail();

        $this->post(route('admin.pengguna.store'), [
            'peran' => Pengguna::ROLE_KEPALA_UNIT, 'id_karyawan' => $karyawan->id_karyawan,
            'username' => 'budi', 'password' => 'Rahasia123', 'status_akun' => 'Aktif',
        ])->assertRedirect(route('admin.pengguna.index'));

        $this->assertDatabaseHas('pengguna', ['username' => 'budi', 'role' => 'kepala_unit']);
        $this->assertSame($karyawan->id_karyawan, $dept->fresh()->id_kepala_unit);
    }

    public function test_pengguna_non_admin_wajib_punya_karyawan_dan_password_kuat(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.pengguna.store'), [
            'peran' => Pengguna::ROLE_KARYAWAN, 'username' => 'tanpa_karyawan', 'password' => 'lemah', 'status_akun' => 'Aktif',
        ])->assertSessionHasErrors(['id_karyawan', 'password']);
    }

    public function test_admin_tidak_bisa_menonaktifkan_akunnya_sendiri(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->put(route('admin.pengguna.update', $admin), [
            'peran' => Pengguna::ROLE_ADMIN, 'username' => 'admin_uji', 'status_akun' => 'Nonaktif',
        ])->assertSessionHas('galat');

        $this->assertSame('Aktif', $admin->fresh()->status_akun);
    }

    public function test_karyawan_nonaktif_menonaktifkan_akunnya(): void
    {
        $this->actingAs($this->admin());
        $dept = $this->dept();
        $k = $this->karyawan($dept);
        $akun = $this->akun(Pengguna::ROLE_KARYAWAN, $k);

        $this->put(route('admin.karyawan.update', $k), [
            'nip' => $k->nip, 'nama_karyawan' => $k->nama_karyawan, 'jabatan' => 'Operator',
            'id_dept' => $dept->id_dept, 'status_karyawan' => 'Nonaktif',
        ])->assertRedirect();

        $this->assertSame('Nonaktif', $akun->fresh()->status_akun);
    }

    public function test_tambah_dan_ubah_shift(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.shift.store'), [
            'kode_shift' => 'S', 'nama_shift' => 'Siang', 'jam_mulai' => '13:00', 'jam_selesai' => '21:00',
            'toleransi_menit' => 15, 'batas_pulang_awal_menit' => 60, 'status_shift' => 'Aktif',
        ])->assertRedirect(route('admin.shift.index'));

        $shift = ShiftKerja::where('kode_shift', 'S')->firstOrFail();
        $this->assertSame('13:00:00', $shift->jam_mulai);

        $this->put(route('admin.shift.update', $shift), [
            'kode_shift' => 'S', 'nama_shift' => 'Siang', 'jam_mulai' => '13:00', 'jam_selesai' => '21:00',
            'toleransi_menit' => 15, 'batas_pulang_awal_menit' => 60, 'status_shift' => 'Nonaktif',
        ])->assertRedirect();
        $this->assertSame('Nonaktif', $shift->fresh()->status_shift);
    }

    public function test_impor_dua_langkah_dari_csv(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());
        $this->shiftPagi();
        $dept = $this->dept();
        $this->karyawan($dept, 'Andi Pratama', ['nip' => '001']);

        $csv = "nip,nama,waktu,shift\n"
            . "001,Andi Pratama,2026-10-01 07:55:00,P\n001,Andi Pratama,2026-10-01 17:05:00,P\n"
            . "001,Andi Pratama,2026-10-02 08:30:00,P\n001,Andi Pratama,2026-10-02 17:00:00,P\n";

        $respons = $this->post(route('admin.absensi.pratinjau'), [
            'periode' => '2026-10',
            'berkas' => UploadedFile::fake()->createWithContent('absen.csv', $csv),
        ])->assertOk()->assertSee('absen.csv');

        $this->assertSame(0, Absensi::count(), 'pratinjau tidak boleh menyimpan data');
        preg_match('/name="token" value="([^"]+)"/', $respons->getContent(), $cocok);
        $this->assertNotEmpty($cocok[1] ?? null);

        $this->post(route('admin.absensi.proses'), [
            'periode' => '2026-10', 'token' => html_entity_decode($cocok[1]), 'nama_file' => 'absen.csv',
        ])->assertRedirect();

        $this->assertSame(2, Absensi::count());
        $this->assertSame('Hadir', Absensi::whereDate('tanggal_absen', '2026-10-01')->firstOrFail()->status_kehadiran);
        $this->assertSame('Terlambat', Absensi::whereDate('tanggal_absen', '2026-10-02')->firstOrFail()->status_kehadiran);
        $this->assertTrue(JadwalShift::whereDate('tanggal_jadwal', '2026-10-01')->exists());
    }

    public function test_token_impor_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.absensi.proses'), [
            'periode' => '2026-10', 'token' => '../../.env', 'nama_file' => 'x.csv',
        ])->assertSessionHasErrors('token');
    }

    public function test_admin_menetapkan_status_dan_kesalahan_selesai(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $k = $this->karyawan($this->dept());
        $a = $this->absensi($k, $this->impor($admin), '2026-10-05', '08:00:00', null, 'Perlu Dicek');
        $this->kesalahan($a);

        $this->put(route('admin.kesalahan.simpan', $a), [
            'status_kehadiran' => 'Hadir', 'jam_masuk' => '08:00', 'jam_pulang' => '17:00', 'catatan' => 'Mesin error',
        ])->assertRedirect();

        $this->assertSame('Hadir', $a->fresh()->status_kehadiran);
        $this->assertSame('Selesai', KesalahanAbsensi::where('id_absensi', $a->id_absensi)->first()->status_perbaikan);
    }

    public function test_alur_konfirmasi_karyawan_sampai_diverifikasi_admin(): void
    {
        $admin = $this->admin();
        $dept = $this->dept();
        $this->shiftPagi();
        $k = $this->karyawan($dept);
        $akunKaryawan = $this->akun(Pengguna::ROLE_KARYAWAN, $k);
        $a = $this->absensi($k, $this->impor($admin), '2026-10-05', '07:58:00', null, 'Perlu Dicek');
        $this->kesalahan($a);

        // karyawan mengirim koreksi jam pulang
        $this->actingAs($akunKaryawan)
            ->post(route('saya.konfirmasi.kirim', $a), [
                'jawaban' => 'Koreksi', 'jam_pulang_koreksi' => '17:02', 'alasan' => 'Lupa scan pulang',
            ])->assertRedirect(route('saya.konfirmasi'));
        $this->assertDatabaseHas('konfirmasi_absensi', ['id_absensi' => $a->id_absensi, 'status_konfir' => 'Menunggu verifikasi']);

        // konfirmasi kedua ditolak karena sudah ada
        $this->post(route('saya.konfirmasi.kirim', $a), ['jawaban' => 'Benar'])->assertRedirect(route('saya.konfirmasi'));

        // admin memverifikasi
        $konfir = KonfirmasiAbsensi::where('id_absensi', $a->id_absensi)->firstOrFail();
        $this->actingAs($admin)
            ->post(route('admin.verifikasi.verifikasi', $konfir), ['catatan_verifikasi' => 'OK'])
            ->assertRedirect(route('admin.verifikasi.index'));

        $a->refresh();
        $this->assertSame('17:02:00', $a->jam_pulang);
        $this->assertSame('Hadir', $a->status_kehadiran);
        $this->assertSame('Terverifikasi', $konfir->fresh()->status_konfir);
        $this->assertSame('Selesai', KesalahanAbsensi::where('id_absensi', $a->id_absensi)->first()->status_perbaikan);
    }

    public function test_penolakan_konfirmasi_wajib_catatan_dan_boleh_dikirim_ulang(): void
    {
        $admin = $this->admin();
        $k = $this->karyawan($this->dept());
        $akunKaryawan = $this->akun(Pengguna::ROLE_KARYAWAN, $k);
        $a = $this->absensi($k, $this->impor($admin), '2026-10-06', null, null, 'Perlu Dicek');
        $this->kesalahan($a, 'Jam masuk kosong');

        $this->actingAs($akunKaryawan)->post(route('saya.konfirmasi.kirim', $a), ['jawaban' => 'Benar']);
        $konfir = KonfirmasiAbsensi::where('id_absensi', $a->id_absensi)->firstOrFail();

        $this->actingAs($admin)->post(route('admin.verifikasi.tolak', $konfir), [])->assertSessionHasErrors('catatan_verifikasi');
        $this->post(route('admin.verifikasi.tolak', $konfir), ['catatan_verifikasi' => 'Bukti kurang'])->assertRedirect();
        $this->assertSame('Ditolak', $konfir->fresh()->status_konfir);

        // karyawan dapat mengirim ulang
        $this->actingAs($akunKaryawan)->post(route('saya.konfirmasi.kirim', $a), [
            'jawaban' => 'Koreksi', 'jam_masuk_koreksi' => '08:00', 'jam_pulang_koreksi' => '17:00', 'alasan' => 'Ada surat tugas',
        ])->assertRedirect(route('saya.konfirmasi'));
        $this->assertSame('Menunggu verifikasi', $konfir->fresh()->status_konfir);
    }

    public function test_karyawan_tidak_bisa_mengonfirmasi_absensi_orang_lain(): void
    {
        $admin = $this->admin();
        $dept = $this->dept();
        $saya = $this->karyawan($dept, 'Saya');
        $lain = $this->karyawan($dept, 'Orang Lain');
        $a = $this->absensi($lain, $this->impor($admin), '2026-10-07', null, null, 'Perlu Dicek');
        $this->kesalahan($a);

        $this->actingAs($this->akun(Pengguna::ROLE_KARYAWAN, $saya))
            ->get(route('saya.konfirmasi.form', $a))->assertNotFound();
    }
}
