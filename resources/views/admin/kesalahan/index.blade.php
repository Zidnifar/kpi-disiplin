@extends('layouts.app')

@section('judul', 'Data Absensi')

@section('isi')
<style>
.kesalahan-wrap {
    width: 100%;
    max-width: none;
    padding: 0 8px 28px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.kesalahan-wrap * {
    box-sizing: border-box;
}
.kesalahan-page-title {
    margin: 0 0 8px;
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
}
.kesalahan-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    margin-bottom: 18px;
}
.kesalahan-breadcrumb a {
    color: #4f6bed;
    text-decoration: none;
    font-weight: 500;
}
.kesalahan-breadcrumb a:hover {
    text-decoration: underline;
}
.kesalahan-breadcrumb span.sep {
    color: #9ca3af;
}
.kesalahan-breadcrumb span.current {
    color: #374151;
    font-weight: 600;
}
.kesalahan-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.03);
}
.kesalahan-card-title {
    margin: 0 0 18px;
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}
.kesalahan-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}
.kesalahan-select {
    min-width: 165px;
    height: 40px;
    padding: 0 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #ffffff;
    color: #1f2937;
    font-size: 13.5px;
    font-weight: 500;
    cursor: pointer;
}
.kesalahan-select:focus {
    outline: none;
    border-color: #ff941c;
}
.kesalahan-table-box {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d1d5db;
    border-radius: 10px;
}
.kesalahan-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 720px;
}
.kesalahan-table thead th {
    background: #f1f5f9;
    color: #111827;
    font-size: 13px;
    font-weight: 700;
    text-align: left;
    padding: 14px 16px;
    border-bottom: 1px solid #d1d5db;
    white-space: nowrap;
}
.kesalahan-table tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 13.5px;
    color: #1f2937;
    vertical-align: middle;
}
.kesalahan-table tbody td:first-child {
    color: #4b5563;
    width: 60px;
}
.kesalahan-table tbody td:nth-child(2) {
    font-weight: 700;
    color: #111827;
}
.kesalahan-table tbody tr:last-child td {
    border-bottom: none;
}
.kesalahan-pill {
    display: inline-flex;
    align-items: center;
    padding: 5px 14px;
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 600;
    white-space: nowrap;
}
.pill-bermasalah {
    background: #fde8e8;
    color: #c81e1e;
}
.pill-perlu-dicek {
    background: #fef0e1;
    color: #b45309;
}
.btn-aksi-konfirmasi {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 96px;
    height: 34px;
    padding: 0 16px;
    border-radius: 8px;
    background: #f97316;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.15s ease;
}
.btn-aksi-konfirmasi:hover {
    background: #ea580c;
}
.kesalahan-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 18px;
    font-size: 13px;
    color: #6b7280;
}
</style>

<div class="kesalahan-wrap">
    <h1 class="kesalahan-page-title">Data Absensi</h1>

    <nav class="kesalahan-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('admin.absensi.index') }}">Data Absensi</a>
        <span class="sep">/</span>
        <span class="current">Periksa Kesalahan</span>
    </nav>

    <div class="kesalahan-card">
        <h2 class="kesalahan-card-title">Pemeriksaan kesalahan data absensi</h2>

        <form method="GET" action="{{ route('admin.kesalahan.index') }}" class="kesalahan-filters" id="filterKesalahanForm">
            <select name="bulan" class="kesalahan-select" onchange="this.form.submit()">
                @php
                    $bulanAktif = $bulan ?? now()->format('Y-m');
                @endphp
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

            <select name="jenis" class="kesalahan-select" onchange="this.form.submit()">
                <option value="">Semua Jenis</option>
                @foreach ($jenisPilihan ?? [] as $jenis)
                    <option value="{{ $jenis }}" @selected(request('jenis') === $jenis)>{{ $jenis }}</option>
                @endforeach
            </select>
        </form>

        <div class="kesalahan-table-box">
            <table class="kesalahan-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th>Jenis Kesalahan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $index => $item)
                        @php
                            $jenisLower = mb_strtolower($item->jenis_kesalahan ?? '');
                            $isPerluDicek = str_contains($jenisLower, 'dicek') || str_contains($jenisLower, 'shift') || str_contains($jenisLower, 'ganda');
                            $statusLabel = $isPerluDicek ? 'Perlu dicek' : 'Bermasalah';
                            $pillClass = $isPerluDicek ? 'pill-perlu-dicek' : 'pill-bermasalah';
                        @endphp
                        <tr>
                            <td>{{ method_exists($daftar, 'firstItem') && $daftar->firstItem() ? $daftar->firstItem() + $index : $index + 1 }}</td>
                            <td>{{ $item->absensi->karyawan->nama_karyawan ?? '—' }}</td>
                            <td>{{ \App\Support\Format::tanggal($item->absensi->tanggal_absen ?? now()) }}</td>
                            <td>{{ $item->jenis_kesalahan ?? '—' }}</td>
                            <td>
                                <span class="kesalahan-pill {{ $pillClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                @if ($item->absensi)
                                    <a href="{{ route('admin.kesalahan.konfirmasi', $item->absensi) }}" class="btn-aksi-konfirmasi">Konfirmasi</a>
                                @else
                                    <span>—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 28px 16px; color: #6b7280;">
                                Tidak ada data kesalahan yang perlu diperiksa pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($daftar, 'total'))
            <div class="kesalahan-footer">
                <div>
                    Menampilkan {{ $daftar->firstItem() ?? 0 }}–{{ $daftar->lastItem() ?? 0 }} dari {{ $daftar->total() }} data
                </div>
                <div>
                    {{ $daftar->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection