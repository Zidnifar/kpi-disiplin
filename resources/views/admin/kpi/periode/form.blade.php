@extends('layouts.app')

@php
    $periode = $periode ?? null;   // form tambah tidak mengirim $periode
    $ubah = $periode !== null;
@endphp
@section('title', $ubah ? 'Ubah Periode' : 'Tambah Periode')
@section('judul', $ubah ? 'Ubah Periode' : 'Tambah Periode')

@section('isi')
<div class="periode-page" style="width: 100%; padding: 0 8px 24px; color: #101828;">

    <!-- Breadcrumb / Navigasi Halaman -->
    <div class="breadcrumb-area" style="margin-bottom: 20px; font-size: 13px; color: #667085;">
        <a href="{{ route('admin.kpi.periode.index') }}" style="color: #667085; text-decoration: none;">Periode KPI</a>
        <span style="margin: 0 6px;">/</span>
        <span style="color: #101828; font-weight: 500;">{{ $ubah ? 'Ubah Periode' : 'Tambah Periode' }}</span>
    </div>

    <!-- Card Formulir -->
    <div class="content-card" style="background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; padding: 28px 32px;">

        <!-- Header Formulir -->
        <div class="form-header" style="margin-bottom: 28px;">
            <h2 style="font-size: 20px; font-weight: 700; color: #101828; margin-bottom: 4px;">
                {{ $ubah ? 'Formulir Ubah Periode Penilaian' : 'Formulir Periode Penilaian' }}
            </h2>
            <p style="font-size: 13px; color: #667085;">Periode dihitung per triwulan. Nilai semester dihitung otomatis dari dua triwulan.</p>
        </div>

        <form method="POST" action="{{ $ubah ? route('admin.kpi.periode.update', $periode->id_periode) : route('admin.kpi.periode.store') }}" novalidate autocomplete="off">
            @csrf
            @if ($ubah) @method('PUT') @endif

            <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px 32px; align-items: start;">

                <!-- BARIS 1 KIRI: Tahun -->
                <div>
                    <label for="tahun" style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Tahun</label>
                    <input id="tahun" name="tahun" type="number" min="2000" max="2100" value="{{ old('tahun', $periode?->tahun ?? now()->year) }}" data-periode-tahun required style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none; background: #fff; box-sizing: border-box;">
                    @error('tahun') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <!-- BARIS 1 KANAN: Triwulan -->
                <div>
                    <label for="triwulan" style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Triwulan</label>
                    <select id="triwulan" name="triwulan" class="custom-select" data-periode-tw required>
                        <option value="">Pilih triwulan</option>
                        @foreach ([1, 2, 3, 4] as $t)
                            <option value="{{ $t }}" @selected((int) old('triwulan', $periode?->triwulan) === $t)>Triwulan {{ $t }}</option>
                        @endforeach
                    </select>
                    @error('triwulan') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <!-- BARIS 2 KIRI: Tanggal Mulai -->
                <div>
                    <label for="tanggal_mulai" style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Tanggal Mulai</label>
                    <input id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ old('tanggal_mulai', $periode?->tanggal_mulai?->format('Y-m-d')) }}" data-periode-mulai style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none; background: #fff; box-sizing: border-box;">
                    <span style="display: block; font-size: 12px; color: #667085; margin-top: 4px;">Kosongkan untuk mengikuti triwulan.</span>
                    @error('tanggal_mulai') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <!-- BARIS 2 KANAN: Tanggal Selesai -->
                <div>
                    <label for="tanggal_selesai" style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Tanggal Selesai</label>
                    <input id="tanggal_selesai" name="tanggal_selesai" type="date" value="{{ old('tanggal_selesai', $periode?->tanggal_selesai?->format('Y-m-d')) }}" data-periode-selesai style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none; background: #fff; box-sizing: border-box;">
                    @error('tanggal_selesai') <div style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <!-- BARIS 3 KIRI: Semester (otomatis) -->
                <div>
                    <label for="semester" style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Semester</label>
                    <input id="semester" type="text" value="{{ $ubah ? 'Semester ' . $periode->semester : 'Terisi otomatis' }}" disabled data-periode-semester style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #e4e7ec; border-radius: 11px; font-size: 14px; color: #667085; background: #f8f9fa; box-sizing: border-box;">
                </div>

                <!-- BARIS 3 KANAN: Status (otomatis) -->
                <div>
                    <label for="status" style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Status</label>
                    <input id="status" type="text" value="{{ $ubah ? ($periode->status_periode ?? 'Berjalan') : 'Berjalan' }}" disabled style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #e4e7ec; border-radius: 11px; font-size: 14px; color: #667085; background: #f8f9fa; box-sizing: border-box;">
                </div>

                <!-- BARIS 4: Tombol Aksi di Kolom Kanan Bawah -->
                <div></div>
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                    <button type="submit" style="height: 44px; padding: 0 28px; border: 0; border-radius: 11px; background: #ff941c; color: #fff; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;">
                        Simpan
                    </button>
                    <a href="{{ route('admin.kpi.periode.index') }}" style="height: 44px; padding: 0 24px; border: 1px solid #d0d5dd; border-radius: 11px; background: #fff; color: #344054; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
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
    box-sizing: border-box;
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