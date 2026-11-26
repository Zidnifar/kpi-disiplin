@extends('layouts.app')

@section('judul', 'Data Absensi')

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

<style>
.konf-wrap {
    width: 100%;
    max-width: none;
    padding: 0 8px 28px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.konf-wrap * {
    box-sizing: border-box;
}
.konf-page-title {
    margin: 0 0 8px;
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
}
.konf-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    margin-bottom: 18px;
}
.konf-breadcrumb a {
    color: #4f6bed;
    text-decoration: none;
    font-weight: 500;
}
.konf-breadcrumb a:hover {
    text-decoration: underline;
}
.konf-breadcrumb span.sep {
    color: #9ca3af;
}
.konf-breadcrumb span.current {
    color: #374151;
    font-weight: 600;
}
.konf-outer {
    background: #eef2f7;
    border-radius: 22px;
    padding: 18px;
}
.konf-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 26px 28px;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
}
.konf-card-title {
    margin: 0 0 18px;
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}
.konf-employee-name {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 3px;
}
.konf-employee-meta {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 22px;
}
.konf-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}
.konf-label {
    display: block;
    font-size: 13.5px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
}
.konf-textarea {
    width: 100%;
    min-height: 120px;
    padding: 12px 14px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    background: #ffffff;
    color: #111827;
    font-size: 13.5px;
    font-family: inherit;
    resize: vertical;
}
.konf-textarea::placeholder {
    color: #9ca3af;
}
.konf-textarea:focus,
.konf-select:focus {
    outline: none;
    border-color: #f97316;
}
.konf-select {
    width: 100%;
    height: 42px;
    padding: 0 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #ffffff;
    color: #111827;
    font-size: 13.5px;
    font-family: inherit;
    cursor: pointer;
}
.konf-error {
    margin-top: 6px;
    font-size: 12.5px;
    color: #dc2626;
    font-weight: 500;
}
.konf-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    margin-top: 22px;
}
.btn-konf-simpan {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 104px;
    height: 36px;
    padding: 0 22px;
    border: none;
    border-radius: 8px;
    background: #f97316;
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s ease;
}
.btn-konf-simpan:hover {
    background: #ea580c;
}
.btn-konf-batal {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 104px;
    height: 36px;
    padding: 0 22px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #ffffff;
    color: #111827;
    font-size: 13.5px;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.btn-konf-batal:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}
@media (max-width: 768px) {
    .konf-grid {
        grid-template-columns: 1fr;
    }
    .konf-actions {
        justify-content: stretch;
    }
    .btn-konf-simpan,
    .btn-konf-batal {
        flex: 1;
    }
}
</style>

<div class="konf-wrap">
    <h1 class="konf-page-title">Data Absensi</h1>

    <nav class="konf-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('admin.absensi.index') }}">Data Absensi</a>
        <span class="sep">/</span>
        <a href="{{ $urlKembali }}">Periksa Kesalahan</a>
        <span class="sep">/</span>
        <span class="current">Konfirmasi Kehadiran</span>
    </nav>

    <div class="konf-outer">
        <div class="konf-card">
            <h2 class="konf-card-title">Konfirmasi kehadiran</h2>

            <div class="konf-employee-name">{{ $namaKaryawan }}</div>
            <div class="konf-employee-meta">{{ $metaBaris }}</div>

            @if ($absensi->konfirmasi)
                <div class="alert alert-info" style="margin-bottom: 18px;">
                    Karyawan sudah mengirim konfirmasi ({{ $absensi->konfirmasi->ringkasanJawaban() }}) — status: {{ $absensi->konfirmasi->status_konfir }}.
                    @if ($absensi->konfirmasi->status_konfir === 'Menunggu verifikasi')
                        <a href="{{ route('admin.verifikasi.show', $absensi->konfirmasi) }}">Buka halaman verifikasi</a>.
                    @endif
                </div>
            @endif

            @if ($selesai)
                <div class="alert alert-sukses" style="margin-bottom: 18px;">
                    Data ini sudah dikonfirmasi. Status akhir: {{ $absensi->status_kehadiran }}.
                </div>
                @if ($catatanAwal !== '')
                    <div class="catatan-kotak" style="margin-bottom: 18px;">{{ $catatanAwal }}</div>
                @endif
                <div class="konf-actions">
                    <a href="{{ $urlKembali }}" class="btn-konf-batal">Kembali</a>
                </div>
            @else
                <form method="POST" action="{{ route('admin.kesalahan.simpan', $absensi) }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="konf-grid">
                        <div>
                            <label for="catatan" class="konf-label">Informasi dari pihak terkait</label>
                            <textarea
                                id="catatan"
                                name="catatan"
                                class="konf-textarea"
                                placeholder="Tulis hasil konfirmasi"
                            >{{ $catatanAwal }}</textarea>
                            @error('catatan')
                                <div class="konf-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="status_kehadiran" class="konf-label">Status kehadiran</label>
                            <select id="status_kehadiran" name="status_kehadiran" class="konf-select" required>
                                <option value="">Pilih status</option>
                                @foreach ($statusPilihan as $s)
                                    <option value="{{ $s }}" @selected(old('status_kehadiran') === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                            @error('status_kehadiran')
                                <div class="konf-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="konf-actions">
                        <button type="submit" class="btn-konf-simpan">Simpan</button>
                        <a href="{{ $urlKembali }}" class="btn-konf-batal">Batal</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection