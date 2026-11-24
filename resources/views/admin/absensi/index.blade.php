@extends('layouts.app')
@section('title', 'Data Absensi Karyawan')
@section('judul', 'Data Absensi')

@section('isi')
<div class="absensi-page" style="width: 100%; padding: 0 8px 24px; color: #101828;">
    
    <!-- Kotak Periode Absensi & Filter (Bagian Atas) -->
    <div class="filter-card-top" style="background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; padding: 20px 24px; margin-bottom: 24px;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px; color: #101828;">Periode Absensi</h3>
        <form action="{{ route('admin.absensi.index') }}" method="GET" style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            
            <!-- Filter Tanggal / Bulan -->
            <div style="flex: 1; min-width: 180px;">
                <input type="month" name="bulan" value="{{ $bulan ?? now()->format('Y-m') }}" style="height: 44px; width: 100%; border: 1px solid #d0dbea; border-radius: 11px; padding: 0 14px; background: #fff; color: #101828; font-size: 14px; outline: none; cursor: pointer;">
            </div>

            <!-- Filter Shift -->
            <div style="flex: 1; min-width: 160px;">
                <select name="shift" class="custom-select">
                    <option value="">Semua shift</option>
                </select>
            </div>

            <!-- Filter Status -->
            <div style="flex: 1; min-width: 160px;">
                <select name="status" class="custom-select">
                    <option value="">Semua status</option>
                    @foreach ($statusPilihan ?? [] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Karyawan -->
            <div style="flex: 1; min-width: 180px;">
                <select name="karyawan" class="custom-select">
                    <option value="">Semua karyawan</option>
                </select>
            </div>

            <!-- Tombol Tampilkan -->
            <button type="submit" style="height: 44px; padding: 0 24px; background: #ff941c; color: #fff; border: 0; border-radius: 11px; font-weight: 600; font-size: 14px; cursor: pointer; transition: background 0.2s;">
                Tampilkan
            </button>
        </form>
    </div>

    <!-- Kartu Ringkasan Statistik -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['total'] ?? 0 }}</h2>
                <p>Daftar Hadir</p>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['terlambat'] ?? 0 }}</h2>
                <p>Terlambat</p>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['belum'] ?? 0 }}</h2>
                <p>Belum dikonfirmasi</p>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['bermasalah'] ?? 0 }}</h2>
                <p>Bermasalah</p>
            </div>
        </div>
    </div>

    <!-- Area Konten Utama & Tabel -->
    <div class="content-card">
        <div class="content-heading">
            <h2>Data absensi karyawan</h2>
            <div class="aksi-tabel" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <!-- Tombol Periksa Kesalahan Dipertahankan -->
                <a href="{{ route('admin.kesalahan.index', ['bulan' => $bulan ?? now()->format('Y-m')]) }}" class="btn-sekunder" style="height: 44px; padding: 0 18px; border: 1px solid #d0d5dd; border-radius: 11px; background: #fff; color: #344054; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Periksa Kesalahan
                </a>
                <a href="{{ route('admin.absensi.impor') }}" class="btn-sekunder" style="height: 44px; padding: 0 18px; border: 1px solid #d0d5dd; border-radius: 11px; background: #fff; color: #344054; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                    Impor Excel
                </a>
                <button type="button" class="btn-tambah" style="height: 44px; padding: 0 20px; border: 0; border-radius: 11px; background: #ff941c; color: #fff; font-size: 14px; font-weight: 600; cursor: pointer;">
                    Tandai perlu konfirmasi
                </button>
            </div>
        </div>

        <!-- Tabel Absensi -->
        <div class="table-wrapper">
            <table class="absensi-table">
                <thead>
                    <tr>
                        <th class="column-no">No</th>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>Status</th>
                        <th style="text-align: center; width: 80px;">Tandai</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($daftar as $index => $a)
                    <tr>
                        <td class="column-no">{{ $daftar->firstItem() + $index }}</td>
                        <td class="nama-column">{{ $a->karyawan->nama_karyawan ?? '—' }}</td>
                        <td>{{ \App\Support\Format::tanggal($a->tanggal_absen) }}</td>
                        <td>{{ $a->jadwal?->shift?->nama_shift ?? 'Pagi' }}</td>
                        <td>{{ \App\Support\Format::jam($a->jam_masuk) }}</td>
                        <td>{{ \App\Support\Format::jam($a->jam_pulang) }}</td>
                        <td>
                            @php
                                $statusClass = match($a->status_kehadiran) {
                                    'Hadir' => 'status-active',
                                    'Terlambat' => 'status-warning',
                                    'Perlu Dicek', 'Bermasalah' => 'status-pending',
                                    default => 'status-active'
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ $a->status_kehadiran }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <input type="checkbox" name="pilih_absen[]" value="{{ $a->id_absensi }}" style="width: 18px; height: 18px; accent-color: #ff941c; cursor: pointer;">
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-data">Belum ada data absensi pada periode ini.</td>
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
.absensi-page {
    width: 100%;
    padding: 0 8px 24px;
    color: #101828;
}

.custom-select {
    height: 44px;
    width: 100%;
    border: 1px solid #d0dbea;
    border-radius: 11px;
    padding: 0 40px 0 14px;
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
    background-position: right 14px center;
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
.status-pending { background: #fee4e2; color: #b42318; }

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