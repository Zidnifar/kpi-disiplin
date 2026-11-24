@extends('layouts.app')
@section('title', 'Data Karyawan')
@section('judul', 'Data Karyawan')

@section('isi')
<div class="karyawan-page">
    <!-- Kartu Ringkasan Statistik -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['total'] ?? 0 }}</h2>
                <p>Total Karyawan</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
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
        <div class="summary-card">
            <div class="summary-info">
                <h2>{{ $ringkasan['departemen'] ?? 0 }}</h2>
                <p>Departemen</p>
            </div>
            <div class="summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Area Konten Utama & Tabel -->
    <div class="content-card">
        <div class="content-heading">
            <h2>Daftar Karyawan</h2>
            <a href="{{ route('admin.karyawan.create') }}" class="btn-tambah" style="text-decoration: none;">
                <span>+</span>
                Tambah Karyawan
            </a>
        </div>

        <!-- Filter Form -->
        <div class="filter-area">
            <form action="{{ route('admin.karyawan.index') }}" method="GET" class="filter-form">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m16 16 4 4"/>
                    </svg>
                    <input type="search" name="q" placeholder="Cari nama, NIP, atau departemen..." value="{{ request('q') }}">
                </div>

                <!-- Select Departemen -->
                <div class="select-wrapper">
                    <select name="dept" onchange="this.form.submit()">
                        <option value="">Semua Departemen</option>
                        @foreach ($departemen as $d)
                            <option value="{{ $d->id_dept }}" @selected(request('dept') == $d->id_dept)>{{ $d->nama_dept }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Status -->
                <div class="select-wrapper">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="Aktif" @selected(request('status') == 'Aktif')>Aktif</option>
                        <option value="Nonaktif" @selected(request('status') == 'Nonaktif')>Nonaktif</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Tabel Karyawan (satu tabel utuh, bisa digeser ke samping) -->
        <div class="table-wrapper">
            <table class="karyawan-table">
                <thead>
                    <tr>
                        <th class="column-no">No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Departemen</th>
                        <th>Jabatan</th>
                        <th>No. Telepon</th>
                        <th>Email</th>
                        <th>Jenis Kelamin</th>
                        <th>Status</th>
                        <th class="column-action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $index => $k)
                    <tr>
                        <td class="column-no">{{ $daftar->firstItem() + $index }}</td>
                        <td>{{ $k->nip ?? '—' }}</td>
                        <td class="nama-column">{{ $k->nama_karyawan }}</td>
                        <td>{{ $k->departemen->nama_dept ?? '—' }}</td>
                        <td>{{ $k->jabatan ?? '—' }}</td>
                        <td>{{ $k->no_hp ?? '-' }}</td>
                        <td>{{ $k->email ?? '-' }}</td>
                        <td>{{ $k->jenis_kelamin ?? '-' }}</td>
                        <td>
                            <span class="status-badge {{ strtolower($k->status_karyawan) == 'aktif' ? 'status-active' : 'status-inactive' }}">
                                {{ ucfirst($k->status_karyawan) }}
                            </span>
                        </td>
                        <td class="column-action">
                            <div class="action-buttons">
                                <a href="{{ route('admin.karyawan.edit', $k->id_karyawan) }}" class="btn-action" title="Ubah">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.karyawan.destroy', $k->id_karyawan) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus karyawan ini?');">
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
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="empty-data">Belum ada data karyawan yang cocok.</td>
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
                <span></span>
            </div>
        </div>
    </div>
</div>

<style>
.karyawan-page {
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

/* Satu tabel utuh: kalau kolom tidak muat, tabel bisa digeser ke samping */
.table-wrapper {
    width: calc(100% - 46px);
    margin: 0 23px 8px;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 6px;
}

.karyawan-table {
    width: 100%;
    min-width: 1250px;
    border-collapse: collapse;
    white-space: nowrap;
}

.karyawan-table th {
    height: 48px;
    background: #f8f9fa;
    color: #344054;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    padding: 0 20px;
    border-bottom: 1px solid #d0d5dd;
}

.karyawan-table td {
    height: 60px;
    padding: 0 20px;
    border-bottom: 1px solid #eef2f6;
    font-size: 14px;
    color: #101828;
    text-align: center;
    vertical-align: middle;
}

.karyawan-table tbody tr:last-child td {
    border-bottom: 0;
}

.karyawan-table .column-no {
    width: 60px;
}

.karyawan-table th.column-action,
.karyawan-table td.column-action {
    width: 120px;
}

.karyawan-table td.nama-column {
    font-weight: 500;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 52px;
    padding: 4px 12px;
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

.empty-data {
    text-align: center !important;
    height: 90px !important;
    color: #667085 !important;
}

.pagination-area {
    padding: 4px 23px 18px;
}

/* Info di kiri, tombol halaman di tengah */
.paginasi {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    width: 100%;
    font-size: 12px;
    color: #667085;
}

.paginasi-tombol {
    display: flex;
    align-items: center;
    gap: 6px;
}

.paginasi-tombol a,
.paginasi-tombol span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 30px;
    padding: 0 8px;
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    background: #fff;
    color: #101828;
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

@media (max-width: 1000px) {
    .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .filter-form { flex-wrap: wrap; }
    .search-box { width: 100%; }
    .paginasi { grid-template-columns: 1fr; gap: 10px; justify-items: center; }
}
</style>
@endsection