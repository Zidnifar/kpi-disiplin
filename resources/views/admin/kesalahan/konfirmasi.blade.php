@extends('layouts.app')

@section('title', 'Konfirmasi Kehadiran')

@section('isi')
@php
    $urlKembali = route('admin.kesalahan.index', ['bulan' => $absensi->tanggal_absen->format('Y-m')]);
    $namaKaryawan = $absensi->karyawan->nama_karyawan ?? '—';
    $tanggalSingkat = $absensi->tanggal_absen
        ? $absensi->tanggal_absen->translatedFormat('j M Y')
        : '—';
    $namaShift = $absensi->jadwal?->shift?->nama_shift;
    $metaBaris = $namaShift ? "{$tanggalSingkat}, {$namaShift}" : $tanggalSingkat;
    $catatanAwal = old('catatan', $absensi->kesalahan->firstWhere('catatan', '!=', null)?->catatan ?? '');
@endphp

<div class="konfirmasi-page" style="width: 100%; padding: 0 8px 24px; color: #101828; box-sizing: border-box;">
    <!-- Breadcrumb / Navigasi Halaman -->
    <div class="breadcrumb-area" style="margin-bottom: 20px; font-size: 13px; color: #667085;">
        <a href="{{ route('admin.absensi.index') }}" style="color: #667085; text-decoration: none;">Data Absensi</a>
        <span style="margin: 0 6px;">/</span>
        <a href="{{ $urlKembali }}" style="color: #667085; text-decoration: none;">Periksa Kesalahan</a>
        <span style="margin: 0 6px;">/</span>
        <span style="color: #101828; font-weight: 500;">Konfirmasi Kehadiran</span>
    </div>

    <!-- Card Formulir -->
    <div class="content-card" style="background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; padding: 28px 32px; box-sizing: border-box;">
        <div class="form-header" style="margin-bottom: 24px;">
            <h2 style="font-size: 20px; font-weight: 700; color: #101828; margin: 0 0 4px;">Konfirmasi Kehadiran</h2>
            <p style="font-size: 13px; color: #667085; margin: 0;">Tetapkan status kehadiran dan informasi konfirmasi untuk data yang bermasalah[cite: 7, 18].</p>
        </div>

        <!-- Info Karyawan -->
        <div style="margin-bottom: 24px; padding-bottom: 18px; border-bottom: 1px solid #eaecf0;">
            <div style="font-size: 15px; font-weight: 700; color: #101828; margin-bottom: 4px;">{{ $namaKaryawan }}[cite: 18]</div>
            <div style="font-size: 13px; color: #667085;">{{ $metaBaris }}[cite: 18]</div>
        </div>

        @if ($absensi->konfirmasi)
            <div style="margin-bottom: 20px; padding: 14px 16px; border-radius: 12px; background: #eff8ff; border: 1px solid #b2ddff; color: #175cd3; font-size: 13px;">
                Karyawan sudah mengirim konfirmasi ({{ $absensi->konfirmasi->ringkasanJawaban() }}) — status: {{ $absensi->konfirmasi->status_konfir }}[cite: 7].
                @if ($absensi->konfirmasi->status_konfir === 'Menunggu verifikasi')
                    <a href="{{ route('admin.verifikasi.show', $absensi->konfirmasi) }}" style="color: #175cd3; font-weight: 600; text-decoration: underline;">Buka halaman verifikasi</a>[cite: 7].
                @endif
            </div>
        @endif

        @if ($selesai)
            <div style="margin-bottom: 20px; padding: 14px 16px; border-radius: 12px; background: #ecfdf3; border: 1px solid #abefc6; color: #067647; font-size: 13px;">
                Data ini sudah dikonfirmasi. Status akhir: <strong>{{ $absensi->status_kehadiran }}</strong>[cite: 7, 18].
            </div>

            @if ($catatanAwal !== '')
                <div style="margin-bottom: 24px; padding: 14px 16px; border-radius: 12px; background: #f9fafb; border: 1px solid #d0dbea; font-size: 13.5px; color: #344054;">
                    {{ $catatanAwal }}[cite: 7]
                </div>
            @endif

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                <a href="{{ $urlKembali }}" style="height: 44px; padding: 0 24px; border: 1px solid #d0d5dd; border-radius: 11px; background: #fff; color: #344054; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                    Kembali[cite: 7]
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('admin.kesalahan.simpan', $absensi) }}" autocomplete="off" novalidate>
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: 1.35fr 1fr; gap: 20px; margin-bottom: 32px; align-items: start;">
                    <div>
                        <label for="catatan" style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Informasi dari pihak terkait[cite: 18]</label>
                        <textarea
                            id="catatan"
                            name="catatan"
                            rows="4"
                            placeholder="Tulis hasil konfirmasi[cite: 18]"
                            style="width: 100%; min-height: 116px; padding: 12px 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; background: #fff; font-family: inherit; resize: vertical; outline: none; box-sizing: border-box;"
                        >{{ $catatanAwal }}</textarea>
                        @error('catatan')
                            <span style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}[cite: 7]</span>
                        @enderror
                    </div>

                    <div>
                        <label for="status_kehadiran" style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Status kehadiran[cite: 18]</label>
                        <select
                            id="status_kehadiran"
                            name="status_kehadiran"
                            required
                            style="width: 100%; height: 44px; padding: 0 42px 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #344054; background-color: #fff; outline: none; cursor: pointer; -webkit-appearance: none; appearance: none; background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 16px center;"
                        >
                            <option value="">Pilih status[cite: 18]</option>
                            @foreach ($statusPilihan as $s)
                                <option value="{{ $s }}" @selected(old('status_kehadiran') === $s)>{{ $s }}[cite: 7]</option>
                            @endforeach
                        </select>
                        @error('status_kehadiran')
                            <span style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}[cite: 7]</span>
                        @enderror
                    </div>
                </div>

                <!-- Tombol Aksi (Batal & Simpan) -->
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                    <a href="{{ $urlKembali }}" style="height: 44px; padding: 0 24px; border: 1px solid #d0d5dd; border-radius: 11px; background: #fff; color: #344054; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                        Batal[cite: 18, 23]
                    </a>
                    <button type="submit" style="height: 44px; padding: 0 24px; border: 0; border-radius: 11px; background: #ff941c; color: #fff; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: background 0.2s;">
                        <span>✓</span> Simpan[cite: 18, 23]
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection