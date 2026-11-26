@extends('layouts.app')
@section('title', 'Periksa Kesalahan')
@section('judul', 'Data Absensi')

@section('isi')
@php
    $bulanAktif = $bulan ?? now()->format('Y-m');
@endphp

<div class="kesalahan-page">
    <!-- Breadcrumb / Navigasi Halaman -->
    <div class="breadcrumb-area">
        <a href="{{ route('admin.absensi.index') }}">Data Absensi</a>
        <span>/</span>
        <span class="aktif">Periksa Kesalahan</span>
    </div>

    <!-- Area Konten Utama & Tabel -->
    <div class="content-card">
        <div class="content-heading">
            <h2>Pemeriksaan kesalahan data absensi</h2>
        </div>

        <!-- Filter Form (Bulan & Jenis Kesalahan) -->
        <div class="filter-area">
            <form action="{{ route('admin.kesalahan.index') }}" method="GET" class="filter-form" id="filterKesalahanForm">
                <div class="select-wrapper">
                    <select name="bulan" onchange="this.form.submit()">
                        <option value="{{ $bulanAktif }}" selected>
                            {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $bulanAktif)->translatedFormat('F Y') }}
                        </option>
                        @for ($i = 0; $i < 12; $i++)
                            @php
                                $opsiBulan = now()->subMonths($i)->format('Y-m');
                            @endphp
                            @if ($opsiBulan !== $bulanAktif)
                                <option value="{{ $opsiBulan }}">
                                    {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $opsiBulan)->translatedFormat('F Y') }}
                                </option>
                            @endif
                        @endfor
                    </select>
                </div>

                <div class="select-wrapper">
                    <select name="jenis" onchange="this.form.submit()">
                        <option value="">Semua Jenis</option>
                        @foreach ($jenisPilihan ?? [] as $jenis)
                            <option value="{{ $jenis }}" @selected(request('jenis') === $jenis)>{{ $jenis }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Tabel Kesalahan -->
        <div class="table-wrapper">
            <table class="kesalahan-table">
                <thead>
                    <tr>
                        <th class="column-no">No</th>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th>Jenis Kesalahan</th>
                        <th>Status</th>
                        <th class="column-action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $index => $item)
                        @php
                            $jenisLower = mb_strtolower($item->jenis_kesalahan ?? '');
                            $isPerluDicek = str_contains($jenisLower, 'dicek') || str_contains($jenisLower, 'shift') || str_contains($jenisLower, 'ganda');
                            $statusLabel = $isPerluDicek ? 'Perlu dicek' : 'Bermasalah';
                            $badgeClass = $isPerluDicek ? 'status-warning' : 'status-danger';
                        @endphp
                        <tr>
                            <td>{{ method_exists($daftar, 'firstItem') && $daftar->firstItem() ? $daftar->firstItem() + $index : $index + 1 }}</td>
                            <td class="nama-column">{{ $item->absensi->karyawan->nama_karyawan ?? '—' }}</td>
                            <td>{{ \App\Support\Format::tanggal($item->absensi->tanggal_absen ?? now()) }}</td>
                            <td>{{ $item->jenis_kesalahan ?? '—' }}</td>
                            <td>
                                <span class="status-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @if ($item->absensi)
                                        <a href="{{ route('admin.kesalahan.konfirmasi', $item->absensi) }}" class="btn-konfirmasi">
                                            Konfirmasi
                                        </a>
                                    @else
                                        <span>—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-data">Tidak ada data kesalahan yang perlu diperiksa pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Area Paginasi Kustom (Inline) -->
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
.kesalahan-page {
    width: 100%;
    padding: 0 8px 24px;
    color: #101828;
    box-sizing: border-box;
}

.breadcrumb-area {
    margin-bottom: 20px;
    font-size: 13px;
    color: #667085;
}

.breadcrumb-area a {
    color: #667085;
    text-decoration: none;
}

.breadcrumb-area span {
    margin: 0 6px;
}

.breadcrumb-area .aktif {
    color: #101828;
    font-weight: 500;
    margin: 0;
}

.content-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 20px;
    overflow: hidden;
}

.content-heading {
    min-height: 64px;
    padding: 20px 23px 8px;
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
    padding: 8px 23px 19px;
}

.filter-form {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
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
    font-weight: 400;
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
}

.kesalahan-table tbody tr:last-child td {
    border-bottom: 0;
}

.kesalahan-table .column-no {
    width: 60px;
    text-align: center;
}

.kesalahan-table tbody td:first-child {
    text-align: center;
}

.kesalahan-table .column-action {
    text-align: center;
    width: 130px;
}

.kesalahan-table td.nama-column {
    font-weight: 600;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-danger {
    background: #fdebed;
    color: #d92d20;
}

.status-warning {
    background: #fef0e1;
    color: #b45309;
}

.action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-konfirmasi {
    height: 36px;
    padding: 0 18px;
    border-radius: 10px;
    background: #ff941c;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.btn-konfirmasi:hover {
    background: #e58316;
}

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