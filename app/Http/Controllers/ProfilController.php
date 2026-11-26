<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    public function tampil(Request $request)
    {
        $pengguna = $request->user()->load('karyawan.departemen');

        return view('profil.tampil', compact('pengguna'));
    }

    public function formPassword()
    {
        return view('profil.password');
    }

    public function ubahPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password_lama' => ['required', 'current_password'],
            'password_baru' => [
                'required',
                'min:8',
                'confirmed',
                'different:password_lama',
                function (string $atribut, mixed $nilai, \Closure $gagal) {
                    $kuat = preg_match('/[a-z]/', (string) $nilai)
                        && preg_match('/[A-Z]/', (string) $nilai)
                        && preg_match('/\d/', (string) $nilai);
                    if (! $kuat) {
                        $gagal('Password baru harus mengandung huruf besar, huruf kecil, dan angka.');
                    }
                },
            ],
        ], [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password_lama.current_password' => 'Password lama tidak sesuai.',
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min' => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak sama.',
            'password_baru.different' => 'Password baru harus berbeda dari password lama.',
        ]);

        // kolom password otomatis di-hash oleh cast "hashed" pada model Pengguna
        $request->user()->update(['password' => $request->input('password_baru')]);

        return redirect()->route('profil')->with('sukses', 'Password berhasil diubah.');
    }

    public function simpanFoto(Request $request)
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'foto.required' => 'Pilih file foto terlebih dahulu.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $file = $request->file('foto');
        $mime = $file->getMimeType();
        $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));

        $pengguna = $request->user();
        $pengguna->foto = $base64;
        $pengguna->save();

        return back()->with('sukses', 'Foto profil berhasil diperbarui.');
    }
}
