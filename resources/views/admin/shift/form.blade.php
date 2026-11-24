@extends('layouts.app')

@php $ubah = $shift !== null; @endphp
@section('title', $ubah ? 'Ubah Jadwal Shift' : 'Tambah Jadwal Shift')
@section('judul', 'Shift Kerja')

@section('isi')
<div class="form-page" style="width: 100%; padding: 0 8px 24px; color: #101828;">
    
    <!-- Breadcrumb / Navigasi Halaman -->
    <div class="breadcrumb-area" style="margin-bottom: 20px; font-size: 13px; color: #667085;">
        <a href="{{ route('admin.shift.index') }}" style="color: #667085; text-decoration: none;">Shift Kerja</a> 
        <span style="margin: 0 6px;">/</span> 
        <span style="color: #101828; font-weight: 500;">{{ $ubah ? 'Ubah Jadwal Shift' : 'Tambah Jadwal Shift' }}</span>
    </div>

    <!-- Card Formulir -->
    <div class="content-card" style="background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; padding: 28px 32px;">
        
        <!-- Header Formulir -->
        <div class="form-header" style="margin-bottom: 24px;">
            <h2 style="font-size: 20px; font-weight: 700; color: #101828; margin-bottom: 4px;">
                {{ $ubah ? 'Formulir Ubah Jadwal Shift' : 'Formulir Tambah Jadwal Shift' }}
            </h2>
            <p style="font-size: 13px; color: #667085;">Lengkapi informasi berikut.</p>
        </div>

        <form method="POST" action="{{ $ubah ? route('admin.shift.update', $shift) : route('admin.shift.store') }}" novalidate autocomplete="off">
            @csrf
            @if ($ubah) @method('PUT') @endif

            <!-- Input Tersembunyi untuk validasi controller -->
            @if(!$ubah)
                <input type="hidden" name="kode_shift" value="S-{{ rand(100,999) }}">
                <input type="hidden" name="toleransi_menit" value="0">
                <input type="hidden" name="batas_pulang_awal_menit" value="60">
                <input type="hidden" name="status_shift" value="Aktif">
            @endif

            <!-- Menggunakan Grid 2 Kolom agar lebar Nama Shift sama persis dengan Jam Mulai di bawahnya -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <!-- Baris 1: Nama Shift (Dibuat seukuran kolom Jam Mulai) -->
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Nama Shift</label>
                    <input id="nama_shift" name="nama_shift" type="text" value="{{ old('nama_shift', $shift?->nama_shift) }}" placeholder="Masukkan nama shift" style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none;" required>
                    @error('nama_shift') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
                <div></div> <!-- Kolom kosong sebelah kanan agar sejajar rapi -->
            </div>

            <!-- Baris 2: Jam Mulai & Jam Selesai (2 Kolom Sejajar) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 32px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Jam Mulai</label>
                    <input id="jam_mulai" name="jam_mulai" type="time" value="{{ old('jam_mulai', $shift ? \App\Support\Format::jam($shift->jam_mulai) : '') }}" style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none; background: #fff; cursor: pointer;" required>
                    @error('jam_mulai') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Jam Selesai</label>
                    <input id="jam_selesai" name="jam_selesai" type="time" value="{{ old('jam_selesai', $shift ? \App\Support\Format::jam($shift->jam_selesai) : '') }}" style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none; background: #fff; cursor: pointer;" required>
                    @error('jam_selesai') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Tombol Aksi (Batal & Simpan di Kanan Bawah) -->
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('admin.shift.index') }}" style="height: 44px; padding: 0 24px; border: 1px solid #d0d5dd; border-radius: 11px; background: #fff; color: #344054; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                    Batal
                </a>
                <button type="submit" style="height: 44px; padding: 0 28px; border: 0; border-radius: 11px; background: #ff941c; color: #fff; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<style>
input[type="time"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: invert(0.5);
}
button[type="submit"]:hover {
    background: #e58316 !important;
}
</style>
@endsection