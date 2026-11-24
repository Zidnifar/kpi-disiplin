<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilTest extends TestCase
{
    use RefreshDatabase;

    private function masuk(): Pengguna
    {
        $pengguna = Pengguna::create([
            'id_karyawan' => null,
            'username' => 'uji',
            'password' => 'Lama12345',
            'role' => Pengguna::ROLE_ADMIN,
            'status_akun' => 'Aktif',
        ]);
        $this->actingAs($pengguna);

        return $pengguna;
    }

    public function test_halaman_profil_tampil(): void
    {
        $this->masuk();
        $this->get('/profil')->assertOk()->assertSee('Informasi Pribadi');
    }

    public function test_ubah_password_berhasil(): void
    {
        $pengguna = $this->masuk();

        $this->put('/profil/password', [
            'password_lama' => 'Lama12345',
            'password_baru' => 'Baru12345',
            'password_baru_confirmation' => 'Baru12345',
        ])->assertRedirect(route('profil'))->assertSessionHas('sukses');

        $this->assertTrue(Hash::check('Baru12345', $pengguna->fresh()->password));
    }

    public function test_password_lama_salah_ditolak(): void
    {
        $this->masuk();

        $this->put('/profil/password', [
            'password_lama' => 'SalahSekali1',
            'password_baru' => 'Baru12345',
            'password_baru_confirmation' => 'Baru12345',
        ])->assertSessionHasErrors('password_lama');
    }

    public function test_password_baru_lemah_ditolak(): void
    {
        $this->masuk();

        foreach (['Pend1A', 'semuahurufkecil1', 'TANPAANGKAsaja'] as $lemah) {
            $this->put('/profil/password', [
                'password_lama' => 'Lama12345',
                'password_baru' => $lemah,
                'password_baru_confirmation' => $lemah,
            ])->assertSessionHasErrors('password_baru');
        }
    }

    public function test_password_baru_harus_berbeda(): void
    {
        $this->masuk();

        $this->put('/profil/password', [
            'password_lama' => 'Lama12345',
            'password_baru' => 'Lama12345',
            'password_baru_confirmation' => 'Lama12345',
        ])->assertSessionHasErrors('password_baru');
    }
}
