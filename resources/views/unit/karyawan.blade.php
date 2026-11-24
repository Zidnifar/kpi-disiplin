@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('judul', 'Data Karyawan')

@section('isi')
@php
    $dataKaryawan = $karyawan ?? ($daftar ?? collect());
    $isPaginator = $dataKaryawan instanceof \Illuminate\Contracts\Pagination\Paginator
        || $dataKaryawan instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    $koleksiKaryawan = $isPaginator ? collect($dataKaryawan->items()) : collect($dataKaryawan);

    $namaDepartemen = $departemen->nama_dept
        ?? $departemen->nama_departemen
        ?? ($namaDept ?? (auth()->user()?->karyawan?->departemen?->nama_dept ?? 'Unit'));

    $totalSemua = (int) ($ringkasan['total'] ?? $ringkasan['total_karyawan'] ?? ($isPaginator ? $dataKaryawan->total() : $koleksiKaryawan->count()));
    $totalAktif = (int) ($ringkasan['aktif'] ?? $koleksiKaryawan->filter(fn ($k) => strtolower((string) ($k->status_karyawan ?? $k->status ?? '')) === 'aktif')->count());
    $totalNonaktif = (int) ($ringkasan['nonaktif'] ?? max(0, $totalSemua - $totalAktif));

    $daftarJabatan = collect($jabatanPilihan ?? $koleksiKaryawan->map(fn ($k) => $k->jabatan)->filter()->unique()->sort()->values());
    $filterCari = (string) request('cari', request('q', ''));
    $filterJabatan = (string) request('jabatan', '');
    $filterStatus = (string) request('status', '');

    if (! $isPaginator) {
        if ($filterCari !== '') {
            $needle = strtolower($filterCari);
            $koleksiKaryawan = $koleksiKaryawan->filter(function ($k) use ($needle) {
                $nip = strtolower((string) ($k->nip ?? ''));
                $nama = strtolower((string) ($k->nama_karyawan ?? $k->nama ?? ''));
                return str_contains($nip, $needle) || str_contains($nama, $needle);
            });
        }
        if ($filterJabatan !== '') {
            $koleksiKaryawan = $koleksiKaryawan->filter(fn ($k) => (string) ($k->jabatan ?? '') === $filterJabatan);
        }
        if ($filterStatus !== '') {
            $koleksiKaryawan = $koleksiKaryawan->filter(fn ($k) => strtolower((string) ($k->status_karyawan ?? $k->status ?? '')) === strtolower($filterStatus));
        }
        $koleksiKaryawan = $koleksiKaryawan->values();
    }

    $dariData = $isPaginator
        ? ($dataKaryawan->total() > 0 ? ($dataKaryawan->firstItem() ?? 1) : 0)
        : ($koleksiKaryawan->count() > 0 ? 1 : 0);
    $sampaiData = $isPaginator
        ? ($dataKaryawan->lastItem() ?? $koleksiKaryawan->count())
        : $koleksiKaryawan->count();
    $totalDitampilkan = $isPaginator ? $dataKaryawan->total() : $koleksiKaryawan->count();
@endphp

<style>
.dk-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 8px 28px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.dk-page * {
    box-sizing: border-box;
}
.dk-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
}

/* 3 Kartu Ringkasan Atas */
.dk-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}
.dk-stat-card {
    min-height: 98px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    min-width: 0;
}
.dk-stat-number {
    font-size: clamp(26px, 2.2vw, 32px);
    font-weight: 700;
    line-height: 1.15;
    color: #111827;
    margin-bottom: 6px;
}
.dk-stat-label {
    font-size: 13px;
    color: #4b5563;
}
.dk-icon-box {
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    border-radius: 12px;
    background: #eef2f6;
    display: flex;
    align-items: center;
    justify-content: center;
}
.dk-icon-box svg {
    width: 21px;
    height: 21px;
    stroke: #111827;
    stroke-width: 1.8;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* Kartu Utama Daftar Karyawan */
.dk-panel {
    padding: 22px 24px;
}
.dk-title {
    margin: 0 0 18px;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

/* Filter Toolbar */
.dk-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.dk-search-wrap {
    position: relative;
    flex: 1 1 260px;
    max-width: 340px;
    min-width: 220px;
}
.dk-search-icon {
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
.dk-input {
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
.dk-input::placeholder {
    color: #9ca3af;
}
.dk-input:focus,
.dk-select:focus {
    border-color: #ff941c;
}

.dk-select {
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

/* Tabel */
.dk-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
}
.dk-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 880px;
}
.dk-table thead {
    background: #eef2f6;
}
.dk-table th {
    height: 44px;
    padding: 0 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
}
.dk-table td {
    height: 54px;
    padding: 0 16px;
    border-top: 1px solid #e5e7eb;
    font-size: 13.5px;
    color: #111827;
    vertical-align: middle;
}
.dk-table tbody tr:hover {
    background: #fafafa;
}
.dk-nama {
    font-weight: 600;
    color: #111827;
}

/* Badge Status */
.dk-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}
.dk-aktif {
    background: #e6f4e9;
    color: #16834f;
}
.dk-nonaktif {
    background: #eef2f6;
    color: #475467;
}

/* Footer Informasi & Paginasi */
.dk-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    margin-top: 16px;
}
.dk-info {
    font-size: 13px;
    color: #6b7280;
}
.dk-pager {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.dk-page-btn {
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
.dk-page-btn:hover:not(.disabled):not(.active) {
    border-color: #ff941c;
    color: #ff941c;
}
.dk-page-btn.active {
    background: #ff941c;
    border-color: #ff941c;
    color: #fff;
}
.dk-page-btn.disabled {
    opacity: 0.45;
    pointer-events: none;
}
.dk-empty {
    text-align: center !important;
    padding: 32px 16px !important;
    color: #6b7280 !important;
    font-size: 14px;
}

@media (max-width: 900px) {
    .dk-stats-grid {
        grid-template-columns: 1fr;
    }
    .dk-search-wrap {
        max-width: none;
    }
}
</style>

<div class="dk-page">
    <!-- 1. 3 Kartu Ringkasan -->
    <section class="dk-stats-grid">
        <div class="dk-card dk-stat-card">
            <div>
                <div class="dk-stat-number">{{ $totalSemua }}</div>
                <div class="dk-stat-label">Total Karyawan</div>
            </div>
            <div class="dk-icon-box">
                <svg viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>

        <div class="dk-card dk-stat-card">
            <div>
                <div class="dk-stat-number">{{ $totalAktif }}</div>
                <div class="dk-stat-label">Aktif</div>
            </div>
            <div class="dk-icon-box">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
            </div>
        </div>

        <div class="dk-card dk-stat-card">
            <div>
                <div class="dk-stat-number">{{ $totalNonaktif }}</div>
                <div class="dk-stat-label">Nonaktif</div>
            </div>
            <div class="dk-icon-box">
                <svg viewBox="0 0 24 24">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- 2. Daftar Karyawan Unit -->
    <section class="dk-card dk-panel">
        <h2 class="dk-title">Daftar karyawan unit {{ $namaDepartemen }}</h2>

        <form method="GET" class="dk-toolbar" id="filterKaryawanForm">
            <div class="dk-search-wrap">
                <svg class="dk-search-icon" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <input
                    type="text"
                    name="cari"
                    value="{{ $filterCari }}"
                    placeholder="Cari nama atau NIP..."
                    class="dk-input"
                    aria-label="Cari nama atau NIP"
                >
            </div>

            <select name="jabatan" onchange="this.form.submit()" class="dk-select" aria-label="Filter Jabatan">
                <option value="">Semua Jabatan</option>
                @foreach ($daftarJabatan as $jab)
                    <option value="{{ $jab }}" @selected($filterJabatan === (string) $jab)>{{ $jab }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="dk-select" aria-label="Filter Status">
                <option value="">Semua Status</option>
                <option value="Aktif" @selected(strtolower($filterStatus) === 'aktif')>Aktif</option>
                <option value="Nonaktif" @selected(strtolower($filterStatus) === 'nonaktif')>Nonaktif</option>
            </select>
        </form>

        <div class="dk-table-wrap">
            <table class="dk-table">
                <thead>
                    <tr>
                        <th style="width: 56px;">No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Departemen</th>
                        <th>Jenis Kelamin</th>
                        <th>No. HP</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($koleksiKaryawan as $row)
                        @php
                            $noUrut = $isPaginator
                                ? (($dataKaryawan->firstItem() ?? 1) + $loop->index)
                                : $loop->iteration;
                            $nip = $row->nip ?? '—';
                            $nama = $row->nama_karyawan ?? $row->nama ?? '—';
                            $jabatan = $row->jabatan ?? '—';
                            $deptRow = $row->departemen?->nama_dept ?? $namaDepartemen;
                            $jk = $row->jenis_kelamin === 'L' ? 'Laki-laki'
                                : ($row->jenis_kelamin === 'P' ? 'Perempuan' : ($row->jenis_kelamin ?? '—'));
                            $noHp = $row->no_hp ?? $row->no_telepon ?? '—';
                            $email = $row->email ?? $row->pengguna?->email ?? '—';
                            $status = $row->status_karyawan ?? $row->status ?? 'Aktif';
                            $isAktif = strtolower((string) $status) === 'aktif';
                        @endphp
                        <tr>
                            <td>{{ $noUrut }}</td>
                            <td>{{ $nip }}</td>
                            <td class="dk-nama">{{ $nama }}</td>
                            <td>{{ $jabatan }}</td>
                            <td>{{ $deptRow }}</td>
                            <td>{{ $jk }}</td>
                            <td>{{ $noHp }}</td>
                            <td>{{ $email }}</td>
                            <td>
                                <span class="dk-badge {{ $isAktif ? 'dk-aktif' : 'dk-nonaktif' }}">
                                    {{ ucfirst(strtolower((string) $status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="dk-empty">Tidak ada data karyawan yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="dk-footer">
            <div class="dk-info">
                Menampilkan {{ $dariData }}-{{ $sampaiData }} dari {{ $totalDitampilkan }} data
            </div>

            @if ($isPaginator && $dataKaryawan->lastPage() > 1)
                @php
                    $current = $dataKaryawan->currentPage();
                    $last = $dataKaryawan->lastPage();
                @endphp
                <div class="dk-pager">
                    <a href="{{ $dataKaryawan->previousPageUrl() ?: '#' }}" class="dk-page-btn {{ $dataKaryawan->onFirstPage() ? 'disabled' : '' }}" aria-label="Halaman Sebelumnya">&lt;</a>
                    @for ($p = 1; $p <= $last; $p++)
                        <a href="{{ $dataKaryawan->url($p) }}" class="dk-page-btn {{ $p === $current ? 'active' : '' }}">{{ $p }}</a>
                    @endfor
                    <a href="{{ $dataKaryawan->nextPageUrl() ?: '#' }}" class="dk-page-btn {{ $dataKaryawan->hasMorePages() ? '' : 'disabled' }}" aria-label="Halaman Berikutnya">&gt;</a>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection