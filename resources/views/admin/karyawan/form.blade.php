@extends('layouts.app')

@php $ubah = isset($karyawan) && $karyawan !== null; @endphp
@section('title', $ubah ? 'Ubah Karyawan' : 'Tambah Karyawan')
@section('judul', 'Data Karyawan')

@section('isi')
<div class="karyawan-page" style="width: 100%; padding: 0 8px 24px; color: #101828;">
    
    <!-- Breadcrumb / Navigasi Halaman -->
    <div class="breadcrumb-area" style="margin-bottom: 20px; font-size: 13px; color: #667085;">
        <a href="{{ route('admin.karyawan.index') }}" style="color: #667085; text-decoration: none;">Data Karyawan</a> 
        <span style="margin: 0 6px;">/</span> 
        <span style="color: #101828; font-weight: 500;">{{ $ubah ? 'Ubah Karyawan' : 'Tambah Karyawan' }}</span>
    </div>

    <!-- Card Formulir -->
    <div class="content-card" style="background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; padding: 28px 32px;">
        
        <!-- Header Formulir -->
        <div class="form-header" style="margin-bottom: 28px;">
            <h2 style="font-size: 20px; font-weight: 700; color: #101828; margin-bottom: 4px;">
                {{ $ubah ? 'Formulir Ubah Pengguna' : 'Formulir Tambah Pengguna' }}
            </h2>
            <p style="font-size: 13px; color: #667085;">Lengkapi informasi berikut.</p>
        </div>

        <form method="POST" action="{{ $ubah ? route('admin.karyawan.update', $karyawan) : route('admin.karyawan.store') }}" novalidate autocomplete="off">
            @csrf
            @if ($ubah) @method('PUT') @endif

            <!-- Layout 2 Kolom (Grid) Sesuai Urutan Baris yang Diminta -->
            <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px 32px; align-items: start;">
                
                <!-- BARIS 1 KIRI: NIP -->
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">NIP</label>
                    <input id="nip" name="nip" type="text" value="{{ old('nip', $karyawan?->nip) }}" placeholder="Masukkan NIP" style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none; background: #fff;">
                    @error('nip') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <!-- BARIS 1 KANAN: Nama Karyawan -->
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Nama Karyawan</label>
                    <input id="nama_karyawan" name="nama_karyawan" type="text" value="{{ old('nama_karyawan', $karyawan?->nama_karyawan) }}" placeholder="Masukkan nama lengkap" style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none; background: #fff;" required>
                    @error('nama_karyawan') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <!-- BARIS 2 KIRI: Jabatan -->
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Jabatan</label>
                    <select id="jabatan" name="jabatan" class="custom-select" required>
                        <option value="" disabled {{ old('jabatan', $karyawan?->jabatan) ? '' : 'selected' }}>Pilih jabatan</option>
                        @foreach ($jabatan as $j)
                            <option value="{{ $j }}" @selected(old('jabatan', $karyawan?->jabatan) === $j)>{{ $j }}</option>
                        @endforeach
                    </select>
                    @error('jabatan') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <!-- BARIS 2 KANAN: Departemen -->
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Departemen</label>
                    <select id="id_dept" name="id_dept" class="custom-select" required>
                        <option value="" disabled {{ old('id_dept', $karyawan?->id_dept) ? '' : 'selected' }}>Pilih departemen</option>
                        @foreach ($departemen as $d)
                            <option value="{{ $d->id_dept }}" @selected(old('id_dept', $karyawan?->id_dept) === $d->id_dept)>{{ $d->nama_dept }}</option>
                        @endforeach
                    </select>
                    @error('id_dept') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <!-- BARIS 3 KIRI: No. Telepon -->
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">No. Telepon</label>
                    <input id="no_hp" name="no_hp" type="text" value="{{ old('no_hp', $karyawan?->no_hp) }}" placeholder="Masukkan nomor telepon" style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none; background: #fff;">
                    @error('no_hp') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <!-- BARIS 3 KANAN: Email -->
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $karyawan?->email) }}" placeholder="Masukkan email" style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none; background: #fff;">
                    @error('email') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <!-- BARIS 4 KIRI: Jenis Kelamin -->
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" class="custom-select">
                        <option value="" disabled {{ old('jenis_kelamin', $karyawan?->jenis_kelamin) ? '' : 'selected' }}>Pilih jenis kelamin</option>
                        <option value="L" @selected(old('jenis_kelamin', $karyawan?->jenis_kelamin) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('jenis_kelamin', $karyawan?->jenis_kelamin) === 'P')>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
                <div></div> <!-- Kosong Kanan Baris 4 -->

                <!-- BARIS 5 KIRI: Status Karyawan & BARIS 5 KANAN: Tombol Aksi -->
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Status Karyawan</label>
                    <div style="display: flex; gap: 24px; height: 44px; align-items: center;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #344054; cursor: pointer;">
                            <input type="radio" name="status_karyawan" value="Aktif" @checked(old('status_karyawan', $karyawan?->status_karyawan ?? 'Aktif') === 'Aktif')" style="width: 16px; height: 16px; accent-color: #ff941c; cursor: pointer;"> Aktif
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #344054; cursor: pointer;">
                            <input type="radio" name="status_karyawan" value="Nonaktif" @checked(old('status_karyawan', $karyawan?->status_karyawan) === 'Nonaktif')" style="width: 16px; height: 16px; accent-color: #ff941c; cursor: pointer;"> Nonaktif
                        </label>
                    </div>
                    @error('status_karyawan') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <!-- Tombol Aksi di Kolom Kanan Bawah -->
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; align-self: flex-end;">
                    <button type="submit" style="height: 44px; padding: 0 28px; border: 0; border-radius: 11px; background: #ff941c; color: #fff; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;">
                        Simpan
                    </button>
                    <a href="{{ route('admin.karyawan.index') }}" style="height: 44px; padding: 0 24px; border: 1px solid #d0d5dd; border-radius: 11px; background: #fff; color: #344054; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                        Batal
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

<style>
.custom-select {
    width: 100%;
    height: 44px;
    padding: 0 42px 0 14px;
    border: 1px solid #d0dbea;
    border-radius: 11px;
    font-size: 14px;
    color: #344054;
    background: #fff;
    outline: none;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
}
button[type="submit"]:hover {
    background: #e58316 !important;
}
</style>
@endsection