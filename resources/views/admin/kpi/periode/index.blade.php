@extends('layouts.app')
@section('title', 'Periode KPI')
@section('judul', 'Periode KPI')

@section('isi')
<div class="periode-page">
    <!-- Kartu Ringkasan -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['total'] ?? 0 }}</h2>
                <p>Total Periode</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['berlangsung'] ?? 0 }}</h2>
                <p>Sedang Berlangsung</p>
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
                <h2>{{ $ringkasan['berakhir'] ?? 0 }}</h2>
                <p>Sudah Berakhir</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18"/>
                    <path d="M8 6V4h8v2"/>
                    <path d="m19 6-1 14H6L5 6"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Area Konten Utama & Tabel -->
    <div class="content-card">
        <div class="content-heading">
            <h2>Periode Penilaian KPI</h2>
            <a href="{{ route('admin.kpi.periode.create') }}" class="btn-tambah" style="text-decoration: none;">
                <span>+</span>
                Tambah Periode
            </a>
        </div>

        <!-- Pencarian -->
        <div class="filter-area">
            <form action="{{ route('admin.kpi.periode.index') }}" method="GET" class="filter-form">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m16 16 4 4"/>
                    </svg>
                    <input type="search" name="q" placeholder="Cari periode atau tahun..." value="{{ request('q') }}">
                </div>
            </form>
        </div>

        @php
            // Tombol aksi selalu tampil. Pastikan route kpi.periode.edit / update / destroy sudah ada di routes/web.php.
            $bisaUbah = true;
            $bisaHapus = true;
        @endphp

        <!-- Tabel Periode -->
        <div class="table-wrapper">
            <table class="periode-table">
                <thead>
                    <tr>
                        <th class="column-no">No</th>
                        <th>Periode</th>
                        <th>Semester</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                        @if ($bisaUbah || $bisaHapus)
                            <th class="column-action">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $index => $p)
                    @php
                        $hari = today();
                        if ($hari->lt($p->tanggal_mulai)) {
                            [$statusTeks, $statusKelas] = ['Mendatang', 'status-upcoming'];
                        } elseif ($hari->gt($p->tanggal_selesai)) {
                            [$statusTeks, $statusKelas] = ['Berakhir', 'status-ended'];
                        } else {
                            [$statusTeks, $statusKelas] = ['Berlangsung', 'status-active'];
                        }
                    @endphp
                    <tr>
                        <td class="column-no">{{ $daftar->firstItem() + $index }}</td>
                        <td class="nama-column">{{ $p->nama_periode }}</td>
                        <td>Semester {{ $p->semester }}</td>
                        <td>{{ \App\Support\Format::tanggal($p->tanggal_mulai) }}</td>
                        <td>{{ \App\Support\Format::tanggal($p->tanggal_selesai) }}</td>
                        <td><span class="status-badge {{ $statusKelas }}">{{ $statusTeks }}</span></td>
                        @if ($bisaUbah || $bisaHapus)
                        <td class="column-action">
                            <div class="action-buttons">
                                @if ($bisaUbah)
                                <a href="{{ route('admin.kpi.periode.edit', $p->id_periode) }}" class="btn-action" title="Ubah">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                    </svg>
                                </a>
                                @endif
                                @if ($bisaHapus)
                                <form action="{{ route('admin.kpi.periode.destroy', $p->id_periode) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus {{ $p->nama_periode }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action" title="Hapus">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="m19 6-1 14H6L5 6"/>
                                            <path d="M10 11v5M14 11v5"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ ($bisaUbah || $bisaHapus) ? 7 : 6 }}" class="empty-data">
                            @if (request('q'))
                                Tidak ada periode yang cocok dengan pencarian.
                            @else
                                Belum ada periode. Tambahkan periode triwulan lebih dulu.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Area Paginasi -->
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
.periode-page {
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

.btn-tambah {
    height: 44px;
    padding: 0 21px;
    border: 0;
    border-radius: 11px;
    background: #ff941c;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-tambah:hover {
    background: #e58316;
}

.btn-tambah span {
    font-size: 22px;
    font-weight: 400;
    line-height: 1;
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
    max-width: 100%;
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

.search-box input::placeholder {
    color: #98a2b3;
}

.table-wrapper {
    width: calc(100% - 46px);
    margin: 0 23px 20px;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 14px;
}

.periode-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.periode-table th {
    height: 48px;
    background: #f8f9fa;
    color: #344054;
    font-size: 13px;
    font-weight: 600;
    text-align: left;
    padding: 0 20px;
    border-bottom: 1px solid #d0d5dd;
}

.periode-table td {
    height: 60px;
    padding: 0 20px;
    border-bottom: 1px solid #eef2f6;
    font-size: 14px;
    color: #101828;
    vertical-align: middle;
}

.periode-table tbody tr:last-child td {
    border-bottom: 0;
}

.periode-table .column-no {
    width: 60px;
    text-align: center;
}

.periode-table tbody td.column-no {
    text-align: center;
    color: #667085;
}

.periode-table th.column-action,
.periode-table td.column-action {
    text-align: center;
    width: 120px;
}

.periode-table td.nama-column {
    font-weight: 500;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 68px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-active {
    background: #e6f4e9;
    color: #16834f;
}

.status-upcoming {
    background: #e8f0fe;
    color: #1d4ed8;
}

.status-ended {
    background: #eef2f6;
    color: #475467;
}

.action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-action {
    width: 36px;
    height: 36px;
    border: 1px solid #d0dbea;
    border-radius: 10px;
    background: #fff;
    color: #344054;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s, border-color 0.2s;
}

.btn-action svg {
    width: 17px;
    height: 17px;
}

.btn-action:hover {
    background: #f8fafc;
    border-color: #98a2b3;
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