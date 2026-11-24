@extends('layouts.app')

@section('title', 'Pemeriksaan KPI')
@section('judul', 'Pemeriksaan KPI')

@section('isi')
@php
    $isPaginator = isset($daftar) && (
        $daftar instanceof \Illuminate\Contracts\Pagination\Paginator
        || $daftar instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
    );
    $koleksiDaftar = $isPaginator ? collect($daftar->items()) : collect($daftar ?? []);

    $dariData = $isPaginator
        ? ($daftar->total() > 0 ? ($daftar->firstItem() ?? 1) : 0)
        : ($koleksiDaftar->count() > 0 ? 1 : 0);
    $sampaiData = $isPaginator
        ? ($daftar->lastItem() ?? $koleksiDaftar->count())
        : $koleksiDaftar->count();
    $totalDitampilkan = $isPaginator ? $daftar->total() : $koleksiDaftar->count();
@endphp

<style>
.pk-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 8px 28px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.pk-page * {
    box-sizing: border-box;
}
.pk-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
}

/* 4 Kartu Ringkasan */
.pk-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}
.pk-stat-card {
    min-height: 98px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    min-width: 0;
}
.pk-stat-number {
    font-size: clamp(26px, 2.2vw, 32px);
    font-weight: 700;
    line-height: 1.15;
    color: #111827;
    margin-bottom: 6px;
}
.pk-stat-label {
    font-size: 13px;
    color: #4b5563;
}
.pk-icon-box {
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    border-radius: 12px;
    background: #eef2f6;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pk-icon-box svg {
    width: 21px;
    height: 21px;
    stroke: #111827;
    stroke-width: 1.8;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* Kartu Utama */
.pk-panel {
    padding: 22px 24px;
}
.pk-title {
    margin: 0 0 18px;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

/* Filter Bar */
.pk-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.pk-search-wrap {
    position: relative;
    flex: 1 1 250px;
    max-width: 320px;
    min-width: 210px;
}
.pk-search-icon {
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
.pk-input {
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
.pk-input::placeholder {
    color: #9ca3af;
}
.pk-select {
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
.pk-input:focus,
.pk-select:focus {
    border-color: #ff941c;
}

/* Tabel */
.pk-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
}
.pk-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 820px;
}
.pk-table thead {
    background: #eef2f6;
}
.pk-table th {
    height: 44px;
    padding: 0 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
}
.pk-table th.tengah,
.pk-table td.tengah {
    text-align: center;
}
.pk-table td {
    height: 54px;
    padding: 0 16px;
    border-top: 1px solid #e5e7eb;
    font-size: 13.5px;
    color: #111827;
    vertical-align: middle;
}
.pk-table tbody tr:hover {
    background: #fafafa;
}
.pk-nama {
    font-weight: 600;
    color: #111827;
}
.pk-minus {
    color: #374151;
    white-space: nowrap;
}

/* Pill Badge */
.pk-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}
.pk-badge-hijau {
    background: #e6f4e9;
    color: #16834f;
}
.pk-badge-biru {
    background: #dbeafe;
    color: #1d4ed8;
}
.pk-badge-merah {
    background: rgba(234, 74, 76, 0.14);
    color: #c62828;
}
.pk-badge-oranye {
    background: rgba(255, 148, 28, 0.16);
    color: #b45309;
}
.pk-badge-netral {
    background: #eef2f6;
    color: #475467;
}

/* Tombol Aksi */
.btn-pk-periksa {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 30px;
    padding: 0 16px;
    border: 0;
    border-radius: 8px;
    background: #ff941c;
    color: #fff;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s ease;
}
.btn-pk-periksa:hover {
    background: #e8830f;
}
.btn-pk-lihat {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 30px;
    padding: 0 16px;
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    background: #fff;
    color: #111827;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.15s ease;
}
.btn-pk-lihat:hover {
    border-color: #ff941c;
    color: #ff941c;
    background: #fffaf4;
}

/* Footer & Paginasi */
.pk-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    margin-top: 16px;
}
.pk-info {
    font-size: 13px;
    color: #6b7280;
}
.pk-pager {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.pk-page-btn {
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
.pk-page-btn:hover:not(.disabled):not(.active) {
    border-color: #ff941c;
    color: #ff941c;
}
.pk-page-btn.active {
    background: #ff941c;
    border-color: #ff941c;
    color: #fff;
}
.pk-page-btn.disabled {
    opacity: 0.45;
    pointer-events: none;
}
.pk-empty {
    text-align: center !important;
    padding: 32px 16px !important;
    color: #6b7280 !important;
    font-size: 14px;
}

@media (max-width: 1100px) {
    .pk-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 640px) {
    .pk-stats-grid {
        grid-template-columns: 1fr;
    }
    .pk-search-wrap {
        max-width: none;
    }
}
</style>

<div class="pk-page">
    @if (! $periode)
        <div class="pk-card pk-panel">
            <p class="pk-info" style="margin: 0;">Belum ada periode penilaian.</p>
        </div>
    @else
        <!-- 1. 4 Kartu Ringkasan -->
        <section class="pk-stats-grid">
            <div class="pk-card pk-stat-card">
                <div>
                    <div class="pk-stat-number">{{ $ringkasan['karyawan'] ?? 0 }}</div>
                    <div class="pk-stat-label">Karyawan Dinilai</div>
                </div>
                <div class="pk-icon-box">
                    <svg viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
            </div>

            <div class="pk-card pk-stat-card">
                <div>
                    <div class="pk-stat-number">{{ $ringkasan['dihitung'] ?? 0 }}</div>
                    <div class="pk-stat-label">Sudah Dihitung</div>
                </div>
                <div class="pk-icon-box">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                </div>
            </div>

            <div class="pk-card pk-stat-card">
                <div>
                    <div class="pk-stat-number">{{ $ringkasan['belum'] ?? 0 }}</div>
                    <div class="pk-stat-label">Belum Diperiksa</div>
                </div>
                <div class="pk-icon-box">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 3"/>
                    </svg>
                </div>
            </div>

            <div class="pk-card pk-stat-card">
                <div>
                    <div class="pk-stat-number">{{ $ringkasan['sudah'] ?? 0 }}</div>
                    <div class="pk-stat-label">Sudah Diperiksa</div>
                </div>
                <div class="pk-icon-box">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                        <rect x="9" y="3" width="6" height="4" rx="1"/>
                    </svg>
                </div>
            </div>
        </section>

        <!-- 2. Tabel Hasil Perhitungan KPI -->
        <section class="pk-card pk-panel">
            <h2 class="pk-title">Hasil perhitungan KPI · {{ $periode->nama_periode }}</h2>

            <form method="GET" class="pk-toolbar" id="filterPemeriksaanForm">
                <div class="pk-search-wrap">
                    <svg class="pk-search-icon" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari nama atau NIP..."
                        class="pk-input"
                        aria-label="Cari nama atau NIP"
                    >
                </div>

                <select name="periode" onchange="this.form.submit()" class="pk-select" aria-label="Pilih Periode">
                    @foreach ($periodeList as $p)
                        <option value="{{ $p->id_periode }}" @selected($p->id_periode === $periode->id_periode)>{{ $p->nama_periode }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" class="pk-select" aria-label="Filter Status Pemeriksaan">
                    <option value="">Semua Status</option>
                    <option value="Belum diperiksa" @selected(request('status') === 'Belum diperiksa')>Belum diperiksa</option>
                    <option value="Sudah diperiksa" @selected(request('status') === 'Sudah diperiksa')>Sudah diperiksa</option>
                </select>
            </form>

            <div class="pk-table-wrap">
                <table class="pk-table">
                    <thead>
                        <tr>
                            <th style="width: 56px;">No</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Poin Pengurang</th>
                            <th>Nilai Triwulan</th>
                            <th>Kategori</th>
                            <th>Pemeriksaan</th>
                            <th class="tengah" style="width: 110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($koleksiDaftar as $h)
                            @php
                                $noUrut = $isPaginator
                                    ? (($daftar->firstItem() ?? 1) + $loop->index)
                                    : $loop->iteration;

                                $namaPredikat = $h->predikat->nama_predikat ?? null;
                                $lowerKat = strtolower((string) $namaPredikat);
                                $kelasKat = str_contains($lowerKat, 'sangat')
                                    ? 'pk-badge-hijau'
                                    : (str_contains($lowerKat, 'baik')
                                        ? 'pk-badge-biru'
                                        : (str_contains($lowerKat, 'kurang') || str_contains($lowerKat, 'buruk') ? 'pk-badge-merah' : 'pk-badge-netral'));

                                $statusPeriksa = (string) ($h->status_periksa ?? 'Belum diperiksa');
                                $belumDiperiksa = strtolower($statusPeriksa) !== 'sudah diperiksa';
                                $kelasPeriksa = $belumDiperiksa ? 'pk-badge-oranye' : 'pk-badge-hijau';
                            @endphp
                            <tr>
                                <td>{{ $noUrut }}</td>
                                <td class="pk-nama">{{ $h->karyawan->nama_karyawan ?? '—' }}</td>
                                <td>{{ $h->karyawan->jabatan ?? '—' }}</td>
                                <td class="pk-minus">&minus; {{ $h->total_pengurang ?? 0 }}</td>
                                <td><strong>{{ \App\Support\Format::nilai($h->nilai_triwulan) }}</strong></td>
                                <td>
                                    @if ($namaPredikat)
                                        <span class="pk-badge {{ $kelasKat }}">{{ $namaPredikat }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="pk-badge {{ $kelasPeriksa }}">{{ $statusPeriksa }}</span>
                                </td>
                                <td class="tengah">
                                    <a
                                        href="{{ route('unit.kpi.pemeriksaan.show', $h) }}"
                                        class="{{ $belumDiperiksa ? 'btn-pk-periksa' : 'btn-pk-lihat' }}"
                                    >
                                        {{ $belumDiperiksa ? 'Periksa' : 'Lihat' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="pk-empty">Belum ada hasil perhitungan KPI untuk unit Anda pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pk-footer">
                <div class="pk-info">
                    Menampilkan {{ $dariData }}-{{ $sampaiData }} dari {{ $totalDitampilkan }} data
                </div>

                @if ($isPaginator && $daftar->lastPage() > 1)
                    @php
                        $current = $daftar->currentPage();
                        $last = $daftar->lastPage();
                    @endphp
                    <div class="pk-pager">
                        <a href="{{ $daftar->previousPageUrl() ?: '#' }}" class="pk-page-btn {{ $daftar->onFirstPage() ? 'disabled' : '' }}" aria-label="Halaman Sebelumnya">&lt;</a>
                        @for ($p = 1; $p <= $last; $p++)
                            <a href="{{ $daftar->url($p) }}" class="pk-page-btn {{ $p === $current ? 'active' : '' }}">{{ $p }}</a>
                        @endfor
                        <a href="{{ $daftar->nextPageUrl() ?: '#' }}" class="pk-page-btn {{ $daftar->hasMorePages() ? '' : 'disabled' }}" aria-label="Halaman Berikutnya">&gt;</a>
                    </div>
                @endif
            </div>
        </section>
    @endif
</div>
@endsection