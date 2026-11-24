<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function buatPengguna(array $ubah = []): Pengguna
    {
        return Pengguna::create(array_merge([
            'id_karyawan' => null,
            'username' => 'uji',
            'password' => 'Rahasia123',
            'role' => Pengguna::ROLE_ADMIN,
            'status_akun' => 'Aktif',
        ], $ubah));
    }

    public function test_halaman_login_tampil(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk');
    }

    public function test_login_berhasil_mengarah_ke_dashboard(): void
    {
        $this->buatPengguna();

        $this->post('/login', ['username' => 'uji', 'password' => 'Rahasia123'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_gagal_bila_password_salah(): void
    {
        $this->buatPengguna();

        $this->post('/login', ['username' => 'uji', 'password' => 'salah'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        $this->buatPengguna(['status_akun' => 'Nonaktif']);

        $this->post('/login', ['username' => 'uji', 'password' => 'Rahasia123'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_tamu_tidak_bisa_membuka_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_dashboard_tampil_untuk_pengguna_login(): void
    {
        $this->actingAs($this->buatPengguna())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Selamat datang');
    }

    public function test_logout_mengeluarkan_pengguna(): void
    {
        $this->actingAs($this->buatPengguna())
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_pembatas_peran(): void
    {
        Route::middleware(['web', 'auth', 'role:kepala_unit'])->get('/_uji-peran', fn () => 'boleh');

        $karyawan = $this->buatPengguna(['username' => 'k1', 'role' => Pengguna::ROLE_KARYAWAN]);
        $kepala = $this->buatPengguna(['username' => 'ku1', 'role' => Pengguna::ROLE_KEPALA_UNIT]);

        $this->actingAs($karyawan)->get('/_uji-peran')->assertForbidden();
        $this->actingAs($kepala)->get('/_uji-peran')->assertOk();
    }
}
