@extends('layouts.app')

@section('judul', 'Data yang perlu dikonfirmasi')

@section('isi')
@php
    $barisKonfirmasi = $daftarKonfirmasi ?? $items ?? collect();

    $urlKembali = \Illuminate\Support\Facades\Route::has('admin.konfirmasi.index')
        ? route('admin.konfirmasi.index')
        : (\Illuminate\Support\Facades\Route::has('admin.verifikasi.index')
            ? route('admin.verifikasi.index')
            : url()->current());
@endphp

<style>
.konfirmasi-page {
    width: 100%;
    max-width: 1120px;
    margin: 0 auto;
    padding: 12px 8px 28px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.konfirmasi-page * {
    box-sizing: border-box;
}
.konfirmasi-panel {
    background: #f3f5f8;
    border-radius: 20px;
    padding: 20px;
}
.konfirmasi-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 24px 26px 26px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
}
.konfirmasi-heading {
    margin: 0 0 18px;
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}
.konfirmasi-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #ffffff;
}
.konfirmasi-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 680px;
}
.konfirmasi-table thead th {
    background: #f3f4f6;
    color: #111827;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    padding: 13px 14px;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}
.konfirmasi-table tbody tr {
    transition: background 0.15s ease;
}
.konfirmasi-table tbody tr:hover {
    background: #fafafa;
}
.konfirmasi-table tbody td {
    padding: 13px 14px;
    border-bottom: 1px solid #eef0f3;
    font-size: 13.5px;
    font-weight: 500;
    color: #1f2937;
    text-align: center;
    vertical-align: middle;
}
.konfirmasi-table tbody tr:last-child td {
    border-bottom: none;
}
.konfirmasi-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 14px;
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
}
.badge-belum {
    background: #fce7d6;
    color: #b4692c;
}
.badge-bermasalah {
    background: #f8d4d8;
    color: #b53b4a;
}
.badge-perlu-dicek {
    background: #dbeafe;
    color: #3b6ea5;
}
.badge-selesai {
    background: #dcfce7;
    color: #15803d;
}
.btn-konfirmasi-aksi {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 18px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #1e293b;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.15s ease;
}
.btn-konfirmasi-aksi:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
.konfirmasi-empty {
    text-align: center !important;
    padding: 28px 16px !important;
    color: #6b7280 !important;
    font-weight: 500 !important;
}
</style>

<div class="konfirmasi-page">
    @if (isset($absensi))
        <div class="breadcrumb" style="margin-bottom: 12px;">
            <a href="{{ $urlKembali }}">Konfirmasi</a> / <strong>Data yang perlu dikonfirmasi</strong>
        </div>
    @endif

    <div class="konfirmasi-panel">
        <div class="konfirmasi-card">
            <h2 class="konfirmasi-heading">Data yang perlu dikonfirmasi</h2>

            <div class="konfirmasi-table-wrap">
                <table class="konfirmasi-table">
                    <thead>
                        <tr>
                            <th style="width: 64px;">No</th>
                            <th>Nama</th>
                            <th>Tanggal</th>
                            <th>Peran</th>
                            <th>Status</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($barisKonfirmasi as $index => $baris)
                            @php
                                $namaKaryawan = data_get($baris, 'karyawan.nama_karyawan')
                                    ?? data_get($baris, 'absensi.karyawan.nama_karyawan')
                                    ?? data_get($baris, 'nama')
                                    ?? '—';

                                $tglRaw = data_get($baris, 'tanggal_absen')
                                    ?? data_get($baris, 'tanggal')
                                    ?? data_get($baris, 'absensi.tanggal_absen');

                                if ($tglRaw instanceof \Carbon\CarbonInterface) {
                                    $tanggalTampil = $tglRaw->translatedFormat('j M');
                                } elseif (!empty($tglRaw)) {
                                    $tanggalTampil = \Illuminate\Support\Carbon::parse($tglRaw)->translatedFormat('j M');
                                } else {
                                    $tanggalTampil = '—';
                                }

                                $peranTampil = data_get($baris, 'peran')
                                    ?? data_get($baris, 'jenis_kesalahan')
                                    ?? data_get($baris, 'keterangan')
                                    ?? 'Perlu konfirmasi';

                                $statusTampil = data_get($baris, 'status_konfir')
                                    ?? data_get($baris, 'status')
                                    ?? data_get($baris, 'status_perbaikan')
                                    ?? 'Belum dikonfirmasi';

                                $statusLower = mb_strtolower($statusTampil);
                                if (str_contains($statusLower, 'bermasalah') || str_contains($statusLower, 'ditolak') || str_contains($statusLower, 'belum diperbaiki')) {
                                    $kelasBadge = 'badge-bermasalah';
                                } elseif (str_contains($statusLower, 'perlu dicek') || str_contains($statusLower, 'menunggu') || str_contains($statusLower, 'proses')) {
                                    $kelasBadge = 'badge-perlu-dicek';
                                } elseif (str_contains($statusLower, 'selesai') || str_contains($statusLower, 'disetujui') || str_contains($statusLower, 'dikonfirmasi')) {
                                    $kelasBadge = 'badge-selesai';
                                } else {
                                    $kelasBadge = 'badge-belum';
                                }

                                if (\Illuminate\Support\Facades\Route::has('admin.konfirmasi.show')) {
                                    $urlAksi = route('admin.konfirmasi.show', $baris);
                                } elseif (\Illuminate\Support\Facades\Route::has('admin.verifikasi.show')) {
                                    $urlAksi = route('admin.verifikasi.show', $baris);
                                } elseif (\Illuminate\Support\Facades\Route::has('admin.konfirmasi.index')) {
                                    $urlAksi = route('admin.konfirmasi.index', ['item' => data_get($baris, 'id')]);
                                } else {
                                    $urlAksi = '#';
                                }
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $namaKaryawan }}</td>
                                <td>{{ $tanggalTampil }}</td>
                                <td>{{ $peranTampil }}</td>
                                <td>
                                    <span class="konfirmasi-badge {{ $kelasBadge }}">{{ $statusTampil }}</span>
                                </td>
                                <td>
                                    <a href="{{ $urlAksi }}" class="btn-konfirmasi-aksi">Konfirmasi</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="konfirmasi-empty">Tidak ada data yang perlu dikonfirmasi saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection