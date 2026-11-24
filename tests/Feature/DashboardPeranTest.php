<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class DashboardPeranTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    public function test_dashboard_tampil_untuk_semua_peran(): void
    {
        $dept = $this->dept();

        $this->actingAs($this->akun(Pengguna::ROLE_ADMIN, null, 'admin_uji'))
            ->get(route('dashboard'))->assertOk()->assertSee('Selamat datang')->assertSee('Perlu Tindakan');

        foreach ([
            Pengguna::ROLE_KARYAWAN, Pengguna::ROLE_KEPALA_UNIT, Pengguna::ROLE_KEPALA_PRODUKSI,
            Pengguna::ROLE_OPERASIONAL_MANAGER, Pengguna::ROLE_GENERAL_MANAGER,
        ] as $peran) {
            $akun = $this->akun($peran, $this->karyawan($dept, 'Orang ' . $peran));
            $this->actingAs($akun)->get(route('dashboard'))->assertOk()->assertSee('Selamat datang');
        }
    }

    public function test_dashboard_admin_menghitung_tugas_yang_menunggu(): void
    {
        $admin = $this->akun(Pengguna::ROLE_ADMIN, null, 'admin_uji');
        $k = $this->karyawan($this->dept());
        $a = $this->absensi($k, $this->impor($admin), '2026-10-05', null, null, 'Perlu Dicek');
        $this->kesalahan($a, 'Jam masuk kosong');

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('Kesalahan absensi belum diperbaiki');
    }
}
