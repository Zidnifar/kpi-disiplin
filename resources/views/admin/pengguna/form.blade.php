@extends('layouts.app')

@php $ubah = $pengguna !== null; @endphp
@section('title', $ubah ? 'Ubah Pengguna' : 'Tambah Pengguna')

@section('isi')
<div class="pengguna-page" style="width: 100%; padding: 0 8px 24px; color: #101828;">
    <!-- Breadcrumb / Navigasi Halaman -->
    <div class="breadcrumb-area" style="margin-bottom: 20px; font-size: 13px; color: #667085;">
        <a href="{{ route('admin.pengguna.index') }}" style="color: #667085; text-decoration: none;">Pengguna</a> 
        <span style="margin: 0 6px;">/</span> 
        <span style="color: #101828; font-weight: 500;">{{ $ubah ? 'Ubah Pengguna' : 'Tambah Pengguna' }}</span>
    </div>

    <!-- Card Formulir -->
    <div class="content-card" style="background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; padding: 28px 32px;">
        <div class="form-header" style="margin-bottom: 24px;">
            <h2 style="font-size: 20px; font-weight: 700; color: #101828; margin-bottom: 4px;">{{ $ubah ? 'Formulir Ubah Pengguna' : 'Formulir Tambah Pengguna' }}</h2>
            <p style="font-size: 13px; color: #667085;">Lengkapi informasi akun berikut.@if ($ubah) Kosongkan password bila tidak ingin mengubahnya.@endif</p>
        </div>

        <form action="{{ $ubah ? route('admin.pengguna.update', $pengguna) : route('admin.pengguna.store') }}" method="POST" autocomplete="off">
            @csrf
            @if ($ubah) @method('PUT') @endif

            <!-- Baris 1: Pilih Karyawan & Username -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Pilih Karyawan</label>
                    <select name="id_karyawan" id="id_karyawan" style="width: 100%; height: 44px; padding: 0 42px 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #344054; background: #fff; outline: none; cursor: pointer; -webkit-appearance: none; appearance: none; background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 16px center;">
                        <option value="">Pilih karyawan</option>
                        @foreach ($karyawanPilihan as $k)
                            <option value="{{ $k->id_karyawan }}" @selected(old('id_karyawan', $pengguna?->id_karyawan) == $k->id_karyawan)>
                                {{ $k->nama_karyawan }} ({{ $k->jabatan ?? 'Karyawan' }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_karyawan')
                        <span style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Username</label>
                    <input type="text" name="username" id="username" placeholder="Masukkan username" value="{{ old('username', $pengguna?->username) }}" autocomplete="off" style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none;" required>
                    @error('username')
                        <span style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Baris 2: Peran & Status Akun -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Peran</label>
                    <select id="peran" name="peran" style="width: 100%; height: 44px; padding: 0 42px 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #344054; background: #fff; outline: none; cursor: pointer; -webkit-appearance: none; appearance: none; background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 16px center;" required>
                        <option value="" disabled {{ old('peran', $pengguna?->role) ? '' : 'selected' }}>Pilih peran pengguna</option>
                        @foreach ($peran as $kode => $nama)
                            <option value="{{ $kode }}" @selected(old('peran', $pengguna?->role) === $kode)>{{ $nama }}</option>
                        @endforeach
                    </select>
                    @error('peran')
                        <span style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Status Akun</label>
                    <select name="status_akun" id="status_akun" style="width: 100%; height: 44px; padding: 0 42px 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #344054; background: #fff; outline: none; cursor: pointer; -webkit-appearance: none; appearance: none; background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 16px center;" required>
                        @foreach (['Aktif', 'Nonaktif'] as $s)
                            <option value="{{ $s }}" @selected(old('status_akun', $pengguna?->status_akun ?? 'Aktif') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                    @error('status_akun')
                        <span style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Baris 3: Password (Lebar Penuh dengan Tombol Mata) -->
            <div style="margin-bottom: 32px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Password</label>
                <div style="position: relative; width: 100%;">
                    <input type="password" name="password" id="password-field" placeholder="{{ $ubah ? 'Kosongkan bila tidak diubah' : 'Buat password' }}" autocomplete="new-password" style="width: 100%; height: 44px; padding: 0 42px 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none;" @if (! $ubah) required @endif>
                    <button type="button" onclick="togglePassword('password-field', 'icon-eye-form')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #667085; display: flex; align-items: center; justify-content: center;">
                        <svg id="icon-eye-form" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
                <span style="font-size: 12px; color: #667085; display: block; margin-top: 4px;">Minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.</span>
                @error('password')
                    <span style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Tombol Aksi (Batal & Simpan) -->
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('admin.pengguna.index') }}" style="height: 44px; padding: 0 24px; border: 1px solid #d0d5dd; border-radius: 11px; background: #fff; color: #344054; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                    Batal
                </a>
                <button type="submit" style="height: 44px; padding: 0 24px; border: 0; border-radius: 11px; background: #ff941c; color: #fff; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: background 0.2s;">
                    <span>✓</span> {{ $ubah ? 'Simpan Perubahan' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('skrip')
<script>
function togglePassword(fieldId, iconId) {
    const passwordInput = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
    } else {
        passwordInput.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    }
}
</script>
@endpush