@extends('layouts.app')

@section('judul', 'Ubah Password')

@section('isi')
    <div class="breadcrumb"><a href="{{ route('profil') }}">Profil</a> / <strong>Ubah Password</strong></div>

    <div class="baris-dua baris-form">
        <div class="kartu kartu-lebar">
            <h2>Ubah Password</h2>
            <p class="redup">Gunakan password yang kuat dan jangan dibagikan kepada siapa pun.</p>

            <form method="POST" action="{{ route('profil.password.update') }}" novalidate>
                @csrf
                @method('PUT')

                <div class="bidang">
                    <label for="password_lama">Password Lama</label>
                    <input id="password_lama" name="password_lama" type="password" autocomplete="current-password" required>
                    @error('password_lama') <div class="galat">{{ $message }}</div> @enderror
                </div>

                <div class="bidang">
                    <label for="password_baru">Password Baru</label>
                    <input id="password_baru" name="password_baru" type="password" autocomplete="new-password" required>
                    @error('password_baru') <div class="galat">{{ $message }}</div> @enderror
                </div>

                <div class="bidang">
                    <label for="password_baru_confirmation">Konfirmasi Password Baru</label>
                    <input id="password_baru_confirmation" name="password_baru_confirmation" type="password" autocomplete="new-password" required>
                </div>

                <div class="aksi-form">
                    <a href="{{ route('profil') }}" class="tombol tombol-sekunder">Batal</a>
                    <button type="submit" class="tombol tombol-utama"><x-icon nama="check" :ukuran="18" /> Simpan Password</button>
                </div>
            </form>
        </div>

        <div class="kartu kartu-samping">
            <h3>Ketentuan Password</h3>
            <ul class="daftar-ikon">
                <li><x-icon nama="checkc" :ukuran="18" /> Minimal 8 karakter</li>
                <li><x-icon nama="checkc" :ukuran="18" /> Kombinasi huruf besar dan kecil</li>
                <li><x-icon nama="checkc" :ukuran="18" /> Mengandung angka</li>
                <li><x-icon nama="checkc" :ukuran="18" /> Berbeda dari password lama</li>
            </ul>
        </div>
    </div>
@endsection
