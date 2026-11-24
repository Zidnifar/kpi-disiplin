@extends('layouts.app')

@section('title', 'Rekap Kehadiran Unit')
@section('judul', 'Rekap Kehadiran Unit')

@section('isi')
@php
    $dataRekap = $rekap ?? ($daftar ?? collect());
    $isPaginator = $dataRekap instanceof \Illuminate\Contracts\Pagination\Paginator
        || $dataRekap instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    $koleksiRekap = $isPaginator ? collect($dataRekap->items()) : collect($dataRekap);

    $labelPeriodeTampil = $labelPeriode ?? now()->locale('id')->translatedFormat('F Y');
    $periodeAktif = (string) ($periodeDipilih ?? request('bulan', request('periode', now()->format('Y-m'))));
    $daftarBulan = collect($bulanPilihan ?? []);
    $filterCari = (string) request('cari', request('q', ''));
    $filterStatus = (string) request('status', '');

    $hariKerja = (int) ($ringkasan['hari_kerja'] ?? 0);
    $rataKehadiran = (float) ($ringkasan['rata_kehadiran'] ?? 0);
    $rataFormatted = str_replace('.', ',', number_format($rataKehadiran, 1, '.', '')) . '%';
    $totalTerlambat = (int) ($ringkasan['total_terlambat'] ?? $koleksiRekap->sum('terlambat'));
    $totalAlpa = (int) ($ringkasan['total_alpa'] ?? $koleksiRekap->sum('alpa'));

    $dariData = $isPaginator
        ? ($dataRekap->total() > 0 ? ($dataRekap->firstItem() ?? 1) : 0)
        : ($koleksiRekap->count() > 0 ? 1 : 0);
    $sampaiData = $isPaginator
        ? ($dataRekap->lastItem() ?? $koleksiRekap->count())
        : $koleksiRekap->count();
    $totalDitampilkan = $isPaginator ? $dataRekap->total() : $koleksiRekap->count();
@endphp

<style>
.rk-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 8px 28px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.rk-page * {
    box-sizing: border-box;
}
.rk-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
}

/* 4 Kartu Ringkasan Atas */
.rk-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}
.rk-stat-card {
    min-height: 98px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    min-width: 0;
}
.rk-stat-number {
    font-size: clamp(24px, 2vw, 32px);
    font-weight: 700;
    line-height: 1.15;
    color: #111827;
    margin-bottom: 6px;
}
.rk-stat-label {
    font-size: 13px;
    color: #4b5563;
}
.rk-icon-box {
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    border-radius: 12px;
    background: #eef2f6;
    display: flex;
    align-items: center;
    justify-content: center;
}
.rk-icon-box svg {
    width: 21px;
    height: 21px;
    stroke: #111827;
    stroke-width: 1.8;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* Kartu Utama Rekap */
.rk-panel {
    padding: 22px 24px;
}
.rk-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.rk-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

/* Filter Toolbar */
.rk-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.rk-search-wrap {
    position: relative;
    flex: 1 1 250px;
    max-width: 320px;
    min-width: 210px;
}
.rk-search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    stroke: #6b7280;
    stroke-width: 2;
    fill: none;
    pointer-events: none;
}
.rk-input {
    width: 100%;
    height: 40px;
    padding: 0 14px 0 38px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    background: #fff;
    color: #111827;
    font-size: 13.5px;
    outline: none;
}
.rk-select {
    height: 40px;
    min-width: 165px;
    padding: 0 36px 0 14px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    background-color: #fff;
    color: #111827;
    font-size: 13.5px;
    font-weight: 500;
    outline: none;
    cursor: pointer;
    -webkit-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23111827' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}
.rk-input:focus,
.rk-select:focus {
    border-color: #ff941c;
}

/* Tabel */
.rk-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
}
.rk-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 820px;
}
.rk-table thead {
    background: #eef2f6;
}
.rk-table th {
    height: 44px;
    padding: 0 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
}
.rk-table td {
    height: 54px;
    padding: 0 16px;
    border-top: 1px solid #e5e7eb;
    font-size: 13.5px;
    color: #111827;
    vertical-align: middle;
}
.rk-table tbody tr:hover {
    background: #fafafa;
}
.rk-nama {
    font-weight: 600;
    color: #111827;
}

/* Badge Keterangan */
.rk-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}
.rk-sangat-baik {
    background: #e6f4e9;
    color: #16834f;
}
.rk-baik {
    background: #dbeafe;
    color: #1d4ed8;
}
.rk-perhatian {
    background: rgba(234, 74, 76, 0.12);
    color: #c62828;
}

/* Footer Informasi & Paginasi */
.rk-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    margin-top: 16px;
}
.rk-info {
    font-size: 13px;
    color: #6b7280;
}
.rk-pager {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.rk-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    background: #fff;
    color: #374151;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}
.rk-page-btn:hover:not(.disabled):not(.active) {
    border-color: #ff941c;
    color: #ff941c;
}
.rk-page-btn.active {
    background: #ff941c;
    border-color: #ff941c;
    color: #fff;
}
.rk-page-btn.disabled {
    opacity: 0.45;
    pointer-events: none;
}
.rk-empty {
    text-align: center !important;
    padding: 32px 16px !important;
    color: #6b7280 !important;
    font-size: 14px;
}

@media (max-width: 1100px) {
    .rk-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 640px) {
    .rk-stats-grid {
        grid-template-columns: 1fr;
    }
    .rk-search-wrap {
        max-width: none;
    }
}
</style>

<div class="rk-page">
    <!-- 1. 4 Kartu Ringkasan -->
    <section class="rk-stats-grid">
        <div class="rk-card rk-stat-card">
            <div>
                <div class="rk-stat-number">{{ $hariKerja }}</div>
                <div class="rk-stat-label">Hari Kerja</div>
            </div>
            <div class="rk-icon-box">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </div>
        </div>

        <div class="rk-card rk-stat-card">
            <div>
                <div class="rk-stat-number">{{ $rataFormatted }}</div>
                <div class="rk-stat-label">Rata-rata Kehadiran</div>
            </div>
            <div class="rk-icon-box">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
            </div>
        </div>

        <div class="rk-card rk-stat-card">
            <div>
                <div class="rk-stat-number">{{ $totalTerlambat }}</div>
                <div class="rk-stat-label">Total Terlambat</div>
            </div>
            <div class="rk-icon-box">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5l3 3"/>
                </svg>
            </div>
        </div>

        <div class="rk-card rk-stat-card">
            <div>
                <div class="rk-stat-number">{{ $totalAlpa }}</div>
                <div class="rk-stat-label">Total Alpa</div>
            </div>
            <div class="rk-icon-box">
                <svg viewBox="0 0 24 24">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- 2. Tabel Rekap Kehadiran -->
    <section class="rk-card rk-panel">
        <div class="rk-header">
            <h2 class="rk-title">Rekap kehadiran {{ $labelPeriodeTampil }}</h2>
        </div>

        <form method="GET" class="rk-toolbar" id="filterKehadiranForm">
            <div class="rk-search-wrap">
                <svg class="rk-search-icon" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <input
                    type="text"
                    name="cari"
                    value="{{ $filterCari }}"
                    placeholder="Cari nama atau NIP..."
                    class="rk-input"
                    aria-label="Cari nama atau NIP"
                >
            </div>

            <select name="periode" onchange="this.form.submit()" class="rk-select" aria-label="Pilih Periode Bulan">
                @foreach ($daftarBulan as $bln)
                    <option value="{{ $bln['value'] }}" @selected($periodeAktif === $bln['value'])>{{ $bln['label'] }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="rk-select" aria-label="Filter Keterangan">
                <option value="">Semua Status</option>
                <option value="Sangat baik" @selected(strtolower($filterStatus) === 'sangat baik')>Sangat baik</option>
                <option value="Baik" @selected(strtolower($filterStatus) === 'baik')>Baik</option>
                <option value="Perlu perhatian" @selected(strtolower($filterStatus) === 'perlu perhatian')>Perlu perhatian</option>
            </select>
        </form>

        <div class="rk-table-wrap">
            <table class="rk-table">
                <thead>
                    <tr>
                        <th style="width: 56px;">No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Hadir</th>
                        <th>Terlambat</th>
                        <th>Izin/Sakit</th>
                        <th>Alpa</th>
                        <th>Persentase</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($koleksiRekap as $row)
                        @php
                            $noUrut = $isPaginator
                                ? (($dataRekap->firstItem() ?? 1) + $loop->index)
                                : $loop->iteration;
                            $ket = (string) ($row->keterangan ?? 'Baik');
                            $ketLower = strtolower($ket);
                            $badgeClass = str_contains($ketLower, 'sangat')
                                ? 'rk-sangat-baik'
                                : (str_contains($ketLower, 'perhatian') ? 'rk-perhatian' : 'rk-baik');
                        @endphp
                        <tr>
                            <td>{{ $noUrut }}</td>
                            <td>{{ $row->nip ?? '—' }}</td>
                            <td class="rk-nama">{{ $row->nama_karyawan ?? '—' }}</td>
                            <td>{{ (int) ($row->hadir ?? 0) }}</td>
                            <td>{{ (int) ($row->terlambat ?? 0) }}</td>
                            <td>{{ (int) ($row->izin_sakit ?? 0) }}</td>
                            <td>{{ (int) ($row->alpa ?? 0) }}</td>
                            <td>{{ (int) ($row->persentase ?? 0) }}%</td>
                            <td>
                                <span class="rk-badge {{ $badgeClass }}">{{ $ket }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="rk-empty">Tidak ada data rekap kehadiran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rk-footer">
            <div class="rk-info">
                Menampilkan {{ $dariData }}-{{ $sampaiData }} dari {{ $totalDitampilkan }} data
            </div>

            @if ($isPaginator && $dataRekap->lastPage() > 1)
                @php
                    $current = $dataRekap->currentPage();
                    $last = $dataRekap->lastPage();
                @endphp
                <div class="rk-pager">
                    <a href="{{ $dataRekap->previousPageUrl() ?: '#' }}" class="rk-page-btn {{ $dataRekap->onFirstPage() ? 'disabled' : '' }}" aria-label="Halaman Sebelumnya">&lt;</a>
                    @for ($p = 1; $p <= $last; $p++)
                        <a href="{{ $dataRekap->url($p) }}" class="rk-page-btn {{ $p === $current ? 'active' : '' }}">{{ $p }}</a>
                    @endfor
                    <a href="{{ $dataRekap->nextPageUrl() ?: '#' }}" class="rk-page-btn {{ $dataRekap->hasMorePages() ? '' : 'disabled' }}" aria-label="Halaman Berikutnya">&gt;</a>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection