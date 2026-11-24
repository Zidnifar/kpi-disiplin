@extends('layouts.app')
@section('title', 'Data Lembur')
@section('judul', 'Data Lembur')

@section('isi')
<div class="lembur-page" style="width: 100%; padding: 0 8px 24px; color: #101828;">
    
    <!-- Kartu Ringkasan Statistik -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['total'] }}</h2>
                <p>Total Pengajuan</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ \App\Support\Format::jumlahJam((float) $ringkasan['jam']) }}</h2>
                <p>Total Jam Disetujui</p>
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
                <h2>{{ $ringkasan['disetujui'] }}</h2>
                <p>Disetujui</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
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
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Area Konten Utama & Tabel -->
    <div class="content-card">
        <div class="content-heading">
            <h2>Rekap lembur {{ \App\Support\Format::bulan($bulan . '-01') }}</h2>
            <div class="aksi-tabel" style="display: flex; align-items: center; gap: 10px;">
                <!-- Tombol Ekspor Excel Warna Kuning -->
                <a href="{{ route('admin.lembur.excel', array_filter(['bulan' => $bulan, 'unit' => request('unit'), 'status' => request('status')])) }}" class="btn-ekspor" style="height: 44px; padding: 0 20px; border: 0; border-radius: 11px; background: #ff941c; color: #fff; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Ekspor Excel
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="filter-area">
            <form action="{{ route('admin.lembur.index') }}" method="GET" class="filter-form">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m16 16 4 4"/>
                    </svg>
                    <input type="search" name="search" placeholder="Cari no. pengajuan..." value="{{ request('search') }}">
                </div>

                <!-- Select Bulan -->
                <div class="select-wrapper" style="width: 180px;">
                    <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" style="height: 44px; width: 100%; border: 1px solid #d0dbea; border-radius: 11px; padding: 0 14px; background: #fff; color: #101828; font-size: 14px; outline: none; cursor: pointer;">
                </div>

                <!-- Select Unit -->
                <div class="select-wrapper" style="width: 200px;">
                    <select name="unit" onchange="this.form.submit()">
                        <option value="">Semua Unit</option>
                        @foreach ($unit as $u)
                            <option value="{{ $u->id_dept }}" @selected(request('unit') === $u->id_dept)>{{ $u->nama_dept }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Status -->
                <div class="select-wrapper" style="width: 220px;">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        @foreach (['Menunggu Ka. Produksi', 'Menunggu Operasional Manager', 'Menunggu General Manager', 'Dikembalikan', 'Disetujui', 'Ditolak', 'Selesai'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Tabel Lembur -->
        <div class="table-wrapper">
            <table class="absensi-table">
                <thead>
                    <tr>
                        <th class="column-no">No</th>
                        <th>No. Pengajuan</th>
                        <th>Unit</th>
                        <th>Tanggal</th>
                        <th class="column-karyawan" style="text-align: center;">Karyawan</th>
                        <th class="column-jam" style="text-align: right;">Rencana</th>
                        <th class="column-jam" style="text-align: right;">Realisasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($daftar as $index => $r)
                    @php $real = method_exists($r, 'totalJamRealisasi') ? $r->totalJamRealisasi() : null; @endphp
                    <tr>
                        <td class="column-no">{{ method_exists($daftar, 'firstItem') && $daftar->firstItem() ? $daftar->firstItem() + $index : $index + 1 }}</td>
                        <td><strong>{{ $r->no_pengajuan }}</strong></td>
                        <td>{{ $r->departemen->nama_dept ?? '—' }}</td>
                        <td>{{ \App\Support\Format::tanggal($r->tanggal_lembur) }}</td>
                        <td style="text-align: center;">{{ method_exists($r, 'detail') ? $r->detail->count() : '-' }}</td>
                        <td style="text-align: right;">{{ method_exists($r, 'totalJamRencana') ? \App\Support\Format::jumlahJam($r->totalJamRencana()) : '-' }}</td>
                        <td style="text-align: right;">{{ $real !== null ? \App\Support\Format::jumlahJam($real) : '—' }}</td>
                        <td>
                            @php
                                $statusClass = match($r->status_rencana) {
                                    'Disetujui', 'Selesai' => 'status-active',
                                    'Menunggu Ka. Produksi', 'Menunggu Operasional Manager', 'Menunggu General Manager', 'Dikembalikan' => 'status-warning',
                                    'Ditolak' => 'status-inactive',
                                    default => 'status-active'
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ $r->status_rencana }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-data">Belum ada pengajuan lembur pada bulan ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Area Paginasi Kustom -->
        <div class="pagination-area">
            <div class="paginasi">
                <span class="paginasi-info">
                    @if (method_exists($daftar, 'total') && $daftar->total() > 0)
                        Menampilkan {{ $daftar->firstItem() }}–{{ $daftar->lastItem() }} dari {{ $daftar->total() }} data
                    @else
                        Tidak ada data
                    @endif
                </span>

                @if (method_exists($daftar, 'hasPages') && $daftar->hasPages())
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
.lembur-page {
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

.btn-ekspor:hover {
    background: #e58316 !important;
}

.filter-area {
    padding: 7px 23px 19px;
}

.filter-form {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.search-box {
    height: 44px;
    width: 300px;
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

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}

.status-active { background: #e6f4e9; color: #16834f; }
.status-warning { background: #fef0c7; color: #b54708; }
.status-inactive { background: #fdebed; color: #d92d20; }

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