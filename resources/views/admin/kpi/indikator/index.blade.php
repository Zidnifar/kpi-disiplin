@extends('layouts.app')
@section('title', 'Indikator KPI')
@section('judul', 'Indikator KPI')

@section('isi')
<div class="indikator-page">
    <!-- Kartu Ringkasan -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['total'] ?? 0 }}</h2>
                <p>Total Kriteria</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 6h16M4 12h16M4 18h10"/>
                </svg>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['aktif'] ?? 0 }}</h2>
                <p>Aktif</p>
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
                <h2>{{ $ringkasan['nonaktif'] ?? 0 }}</h2>
                <p>Nonaktif</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <path d="m6 6 12 12M18 6 6 18"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Area Konten Utama & Tabel -->
    <div class="content-card">
        <div class="content-heading">
            <h2>Kriteria Disiplin</h2>
        </div>

        <!-- Pencarian dan tombol tambah (sejajar) -->
        <div class="filter-area">
            <div class="toolbar">
                <form action="{{ route('admin.kpi.indikator.index') }}" method="GET" class="filter-form">
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m16 16 4 4"/>
                        </svg>
                        <input type="search" name="q" placeholder="Cari kode atau kriteria..." value="{{ request('q') }}">
                    </div>
                </form>

                <a href="{{ route('admin.kpi.indikator.create') }}" class="btn-tambah" style="text-decoration: none;">
                    <span>+</span>
                    Tambah Kriteria
                </a>
            </div>
        </div>

        <!-- Tabel Kriteria -->
        <div class="table-wrapper">
            <table class="indikator-table">
                <thead>
                    <tr>
                        <th class="column-no">No</th>
                        <th>Kode</th>
                        <th>Kriteria</th>
                        <th class="column-bobot">Bobot (poin)</th>
                        <th>Status</th>
                        <th class="column-action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $index => $k)
                    @php
                        $sistem = in_array($k->kode_indikator, $kodeSistem, true);
                        $terpakai = $dipakai->contains($k->id_indikator);
                        $hapusTerkunci = $sistem || $terpakai;
                        $alasanKunci = $sistem ? 'Dipakai aturan absensi, tidak dapat dihapus' : 'Sudah dipakai pada data pelanggaran atau hasil KPI, tidak dapat dihapus';
                    @endphp
                    <tr>
                        <td class="column-no">{{ $daftar->firstItem() + $index }}</td>
                        <td class="kode-column">{{ $k->kode_indikator }}</td>
                        <td>{{ $k->nama_indikator }}</td>
                        <td class="column-bobot">{{ $k->bobot_poin }}</td>
                        <td>
                            <span class="status-badge {{ $k->status_indikator === 'Aktif' ? 'status-active' : 'status-inactive' }}">{{ $k->status_indikator }}</span>
                        </td>
                        <td class="column-action">
                            <div class="action-buttons">
                                <a href="{{ route('admin.kpi.indikator.edit', $k) }}" class="btn-action" title="Ubah">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                    </svg>
                                </a>
                                @if ($hapusTerkunci)
                                    <span class="btn-action btn-disabled" title="{{ $alasanKunci }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="m19 6-1 14H6L5 6"/>
                                            <path d="M10 11v5M14 11v5"/>
                                        </svg>
                                    </span>
                                @else
                                    <form action="{{ route('admin.kpi.indikator.destroy', $k) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus kriteria {{ $k->kode_indikator }}?');">
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-data">
                            @if (request('q'))
                                Tidak ada kriteria yang cocok dengan pencarian.
                            @else
                                Belum ada kriteria. Tambahkan kriteria disiplin lebih dulu.
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
.indikator-page {
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
    white-space: nowrap;
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

.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.filter-form {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1;
    min-width: 0;
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

.indikator-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.indikator-table th {
    height: 48px;
    background: #f8f9fa;
    color: #344054;
    font-size: 13px;
    font-weight: 600;
    text-align: left;
    padding: 0 20px;
    border-bottom: 1px solid #d0d5dd;
}

.indikator-table td {
    height: 60px;
    padding: 0 20px;
    border-bottom: 1px solid #eef2f6;
    font-size: 14px;
    color: #101828;
    vertical-align: middle;
}

.indikator-table tbody tr:last-child td {
    border-bottom: 0;
}

.indikator-table .column-no {
    width: 60px;
    text-align: center;
}

.indikator-table tbody td.column-no {
    text-align: center;
    color: #667085;
}

.indikator-table th.column-bobot,
.indikator-table td.column-bobot {
    text-align: center;
    width: 130px;
}

.indikator-table th.column-action,
.indikator-table td.column-action {
    text-align: center;
    width: 120px;
}

.indikator-table td.kode-column {
    font-weight: 600;
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

.status-inactive {
    background: #fdebed;
    color: #d92d20;
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

.btn-action.btn-disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #f8f9fa;
}

.btn-action.btn-disabled:hover {
    background: #f8f9fa;
    border-color: #d0dbea;
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