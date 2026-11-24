@extends('layouts.app')
@section('title', 'Pemeriksaan Kesalahan Data Absensi')
@section('judul', 'Pemeriksaan Kesalahan Data Absensi')

@section('isi')
<div class="kesalahan-page" style="width: 100%; padding: 0 8px 24px; color: #101828;">
    <!-- Kartu Ringkasan Statistik -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['total'] }}</h2>
                <p>Total Kesalahan</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['tidak_excel'] }}</h2>
                <p>Tidak di Excel</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="12" y1="18" x2="12" y2="12"/>
                    <line x1="9" y1="15" x2="15" y2="15"/>
                </svg>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['belum'] }}</h2>
                <p>Belum Diperbaiki</p>
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
                <h2>{{ $ringkasan['selesai'] }}</h2>
                <p>Sudah Diperbaiki</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 11.2V12a8 8 0 1 1-4.7-7.3"/>
                    <path d="m8.5 11.5 3 3L21 5"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Area Konten Utama & Tabel -->
    <div class="content-card">
        <div class="content-heading">
            <h2>Pemeriksaan kesalahan data absensi</h2>
            <a href="{{ route('admin.absensi.index', ['bulan' => $bulan]) }}" class="btn-sekunder" style="height: 44px; padding: 0 20px; border: 1px solid #d0d5dd; border-radius: 11px; background: #fff; color: #344054; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Data Absensi
            </a>
        </div>

        <!-- Filter Form -->
        <div class="filter-area">
            <form action="{{ route('admin.kesalahan.index') }}" method="GET" class="filter-form">
                <!-- Select Bulan -->
                <div class="select-wrapper">
                    <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" style="height: 44px; width: 100%; border: 1px solid #d0dbea; border-radius: 11px; padding: 0 14px; background: #fff; color: #101828; font-size: 14px; outline: none; cursor: pointer;">
                </div>

                <!-- Select Jenis Kesalahan -->
                <div class="select-wrapper">
                    <select name="jenis" onchange="this.form.submit()">
                        <option value="">Semua Jenis</option>
                        @foreach ($jenisPilihan as $j)
                            <option value="{{ $j }}" @selected(request('jenis') === $j)>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Status -->
                <div class="select-wrapper">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="Belum" @selected(request('status') === 'Belum')>Belum diperbaiki</option>
                        <option value="Selesai" @selected(request('status') === 'Selesai')>Sudah diperbaiki</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Tabel Pemeriksaan Kesalahan -->
        <div class="table-wrapper">
            <table class="kesalahan-table">
                <thead>
                    <tr>
                        <th class="column-no">No</th>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th>Masalah</th>
                        <th>Status</th>
                        <th class="column-action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($daftar as $index => $k)
                    @php
                        $selesai = $k->status_perbaikan === 'Selesai';
                        $konfir = $k->absensi->konfirmasi ?? null;
                    @endphp
                    <tr>
                        <td class="column-no">{{ $daftar->firstItem() + $index }}</td>
                        <td class="nama-column">{{ $k->absensi->karyawan->nama_karyawan ?? '—' }}</td>
                        <td>{{ \App\Support\Format::tanggal($k->absensi->tanggal_absen) }}</td>
                        <td>{{ $k->jenis_kesalahan }}</td>
                        <td>
                            @php
                                $statusClass = $selesai ? 'status-active' : 'status-warning';
                                $statusText = $selesai ? 'Sudah diperbaiki' : 'Belum diperbaiki';
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                            @if ($konfir && ! $selesai)
                                <span style="font-size: 11px; color: #667085; display: block; margin-top: 2px;">Konfirmasi: {{ $konfir->status_konfir }}</span>
                            @endif
                        </td>
                        <td class="column-action">
                            @if ($selesai)
                                <a class="btn-aksi-sekunder" href="{{ route('admin.kesalahan.konfirmasi', $k->absensi) }}">Lihat</a>
                            @elseif ($konfir && $konfir->status_konfir === 'Menunggu verifikasi')
                                <a class="btn-aksi-utama" href="{{ route('admin.verifikasi.show', $konfir) }}">Verifikasi</a>
                            @else
                                <a class="btn-aksi-utama" href="{{ route('admin.kesalahan.konfirmasi', $k->absensi) }}">Konfirmasi</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-data">Tidak ada kesalahan data pada bulan ini.</td>
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
.kesalahan-page {
    width: 100%;
    padding: 0 8px 24px;
    color: #101828;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
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

.select-wrapper {
    position: relative;
    display: inline-block;
    width: 220px;
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

.kesalahan-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.kesalahan-table th {
    height: 48px;
    background: #f8f9fa;
    color: #344054;
    font-size: 13px;
    font-weight: 600;
    text-align: left;
    padding: 0 20px;
    border-bottom: 1px solid #d0d5dd;
}

.kesalahan-table td {
    height: 60px;
    padding: 0 20px;
    border-bottom: 1px solid #eef2f6;
    font-size: 14px;
    color: #101828;
    vertical-align: middle;
}

.kesalahan-table tbody tr:last-child td {
    border-bottom: 0;
}

.kesalahan-table .column-no {
    width: 60px;
    text-align: center;
}

.kesalahan-table tbody td.column-no {
    text-align: center;
    color: #667085;
}

.kesalahan-table th.column-action,
.kesalahan-table td.column-action {
    text-align: center;
    width: 130px;
}

.kesalahan-table td.nama-column {
    font-weight: 500;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 100px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-active { background: #e6f4e9; color: #16834f; }
.status-warning { background: #fef0c7; color: #b54708; }

.btn-aksi-utama {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    padding: 0 16px;
    background: #ff941c;
    color: #fff;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s;
}
.btn-aksi-utama:hover { background: #e58316; }

.btn-aksi-sekunder {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    padding: 0 16px;
    background: #fff;
    color: #344054;
    border: 1px solid #d0d5dd;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s;
}
.btn-aksi-sekunder:hover { background: #f8fafc; }

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