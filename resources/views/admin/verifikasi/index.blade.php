@extends('layouts.app')
@section('title', 'Verifikasi Konfirmasi Kehadiran')
@section('judul', 'Verifikasi Konfirmasi Kehadiran')

@section('isi')
<div class="verifikasi-page" style="width: 100%; padding: 0 8px 24px; color: #101828;">
    <!-- Kartu Ringkasan Statistik -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['menunggu'] }}</h2>
                <p>Menunggu Verifikasi</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['terverifikasi'] }}</h2>
                <p>Terverifikasi</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 11.2V12a8 8 0 1 1-4.7-7.3"/>
                    <path d="m8.5 11.5 3 3L21 5"/>
                </svg>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['ditolak'] }}</h2>
                <p>Ditolak</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Area Konten Utama & Tabel -->
    <div class="content-card">
        <div class="content-heading">
            <h2>Daftar Konfirmasi Kehadiran {{ \App\Support\Format::bulan($bulan . '-01') }}</h2>
        </div>

        <!-- Filter Form -->
        <div class="filter-area">
            <form action="{{ route('admin.verifikasi.index') }}" method="GET" class="filter-form">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m16 16 4 4"/>
                    </svg>
                    <input type="search" name="q" placeholder="Cari nama atau NIP..." value="{{ request('q') }}">
                </div>

                <!-- Select Bulan -->
                <div class="select-wrapper">
                    <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" style="height: 44px; width: 100%; border: 1px solid #d0dbea; border-radius: 11px; padding: 0 14px; background: #fff; color: #101828; font-size: 14px; outline: none; cursor: pointer;">
                </div>

                <!-- Select Status -->
                <div class="select-wrapper">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        @foreach (['Menunggu verifikasi', 'Terverifikasi', 'Ditolak'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Tabel Verifikasi -->
        <div class="table-wrapper">
            <table class="absensi-table">
                <thead>
                    <tr>
                        <th class="column-no">No</th>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th>Konfirmasi Karyawan</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th class="column-action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($daftar as $index => $k)
                    <tr>
                        <td class="column-no">{{ $daftar->firstItem() + $index }}</td>
                        <td class="nama-column">{{ $k->absensi->karyawan->nama_karyawan ?? '—' }}</td>
                        <td>{{ \App\Support\Format::tanggal($k->absensi->tanggal_absen) }}</td>
                        <td>{{ $k->ringkasanJawaban() }}</td>
                        <td>{{ $k->alasan ?? '—' }}</td>
                        <td>
                            @php
                                $statusClass = match($k->status_konfir) {
                                    'Terverifikasi' => 'status-active',
                                    'Menunggu verifikasi' => 'status-warning',
                                    'Ditolak' => 'status-inactive',
                                    default => 'status-active'
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ $k->status_konfir }}
                            </span>
                        </td>
                        <td class="column-action">
                            <a href="{{ route('admin.verifikasi.show', $k) }}" class="btn-action" title="{{ $k->status_konfir === 'Menunggu verifikasi' ? 'Verifikasi' : 'Lihat' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-data">Belum ada konfirmasi kehadiran pada bulan ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Area Paginasi Kustom -->
        <div class="pagination-area">
            <div class="paginasi">
                <span class="paginasi-info">
                    @if ($daftar->total() > 0)
                        Menampilkan {{ $daftar->firstItem() }}–{{ $daftar->lastItem() }} dari {{ $daftar->total() }} data
                    @else
                        Tidak ada data
                    @endif
                </span>

                @if ($daftar->hasPages())
                    <nav class="paginasi-tombol" role="navigation" aria-label="Halaman">
                        @if ($daftar->onFirstPage())
                            <span class="nonaktif">‹</span>
                        @else
                            <a href="{{ $daftar->previousPageUrl() }}" rel="prev">‹</a>
                        @endif

                        @foreach ($daftar->linkCollection() as $link)
                            @if ($link['url'] === null)
                                @if ($link['label'] === '...')
                                    <span class="nonaktif">...</span>
                                @endif
                            @else
                                @if ($link['active'])
                                    <span class="aktif">{{ $link['label'] }}</span>
                                @else
                                    <a href="{{ $link['url'] }}">{!! $link['label'] == '&laquo; Previous' ? '‹' : ($link['label'] == 'Next &raquo;' ? '›' : $link['label']) !!}</a>
                                @endif
                            @endif
                        @endforeach

                        @if ($daftar->hasMorePages())
                            <a href="{{ $daftar->nextPageUrl() }}" rel="next">›</a>
                        @else
                            <span class="nonaktif">›</span>
                        @endif
                    </nav>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.verifikasi-page {
    width: 100%;
    padding: 0 8px 24px;
    color: #101828;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.summary-card {
    min-height: 96px;
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 20px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.summary-info h2 {
    font-size: 34px;
    line-height: 1.1;
    font-weight: 700;
    color: #000;
    margin: 0 0 5px;
}

.summary-info p {
    font-size: 13px;
    color: #344054;
    margin: 0;
}

.summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 15px;
    background: #eef2f6;
    color: #101828;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.summary-icon svg {
    width: 25px;
    height: 25px;
}

.content-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 20px;
    overflow: hidden;
}

.content-heading {
    min-height: 70px;
    padding: 18px 23px 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.content-heading h2 {
    font-size: 20px;
    font-weight: 600;
    color: #101828;
    margin: 0;
}

.filter-area {
    padding: 7px 23px 19px;
}

.filter-form {
    display: flex;
    align-items: center;
    gap: 16px;
}

.search-box {
    height: 44px;
    width: 380px;
    border: 1px solid #d0dbea;
    border-radius: 11px;
    display: flex;
    align-items: center;
    padding: 0 13px;
    flex-shrink: 0;
    background: #fff;
}

.search-box svg {
    width: 19px;
    height: 19px;
    color: #101828;
    flex-shrink: 0;
    margin-right: 10px;
}

.search-box input {
    width: 100%;
    border: 0;
    outline: 0;
    font-size: 14px;
    color: #344054;
    background: transparent;
}

.select-wrapper {
    position: relative;
    display: inline-block;
    width: 200px;
}

.select-wrapper select {
    height: 44px;
    width: 100%;
    border: 1px solid #d0dbea;
    border-radius: 11px;
    padding: 0 42px 0 14px;
    background: #fff;
    color: #101828;
    font-size: 14px;
    outline: none;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
}

.table-wrapper {
    width: calc(100% - 46px);
    margin: 0 23px 20px;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 14px;
}

.absensi-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.absensi-table th {
    height: 48px;
    background: #f8f9fa;
    color: #344054;
    font-size: 13px;
    font-weight: 600;
    text-align: left;
    padding: 0 20px;
    border-bottom: 1px solid #d0d5dd;
}

.absensi-table td {
    height: 60px;
    padding: 0 20px;
    border-bottom: 1px solid #eef2f6;
    font-size: 14px;
    color: #101828;
    vertical-align: middle;
}

.absensi-table tbody tr:last-child td {
    border-bottom: 0;
}

.absensi-table .column-no {
    width: 60px;
    text-align: center;
}

.absensi-table tbody td.column-no {
    text-align: center;
    color: #667085;
}

.absensi-table th.column-action,
.absensi-table td.column-action {
    text-align: center;
    width: 100px;
}

.absensi-table td.nama-column {
    font-weight: 500;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 75px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-active { background: #e6f4e9; color: #16834f; }
.status-warning { background: #fef0c7; color: #b54708; }
.status-inactive { background: #fdebed; color: #d92d20; }

.btn-action {
    width: 36px;
    height: 36px;
    border: 1px solid #d0dbea;
    border-radius: 10px;
    background: #fff;
    color: #344054;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
    transition: 0.2s;
}

.btn-action svg { width: 17px; height: 17px; }
.btn-action:hover { background: #f8fafc; border-color: #98a2b3; }

.empty-data {
    text-align: center !important;
    height: 90px !important;
    color: #667085 !important;
}

.pagination-area {
    min-height: 55px;
    padding: 16px 23px;
}

.paginasi {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    font-size: 13px;
    color: #667085;
}

.paginasi-tombol {
    display: flex;
    align-items: center;
    gap: 4px;
}

.paginasi-tombol a,
.paginasi-tombol span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    background: #fff;
    color: #344054;
    font-size: 13px;
    text-decoration: none;
    transition: 0.2s;
}

.paginasi-tombol span.aktif {
    background: #ff941c;
    color: #fff;
    border-color: #ff941c;
    font-weight: 600;
}

.paginasi-tombol span.nonaktif {
    background: #f8f9fa;
    color: #98a2b3;
    cursor: not-allowed;
}

.paginasi-tombol a:hover {
    background: #f8fafc;
    border-color: #98a2b3;
}
</style>
@endsection