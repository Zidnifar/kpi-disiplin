@extends('layouts.app')

@section('title', 'Status Lembur Saya')
@section('judul', 'Status Lembur Saya')

@section('isi')
@php
    $tahapLembur = [
        ['Diajukan', 'Kepala Unit'],
        ['Diperiksa', 'Kepala Produksi'],
        ['Diketahui', 'Operasional Manager'],
        ['Keputusan', 'General Manager'],
    ];

    $langkahAktif = 0;
    if ($terbaru) {
        $langkahAktif = \App\Services\DashboardKaryawanService::langkahLembur($terbaru->status_rencana);
    }

    $statusTerbaru = strtolower((string) ($terbaru->status_rencana ?? ''));
    $badgeTerbaru = str_contains($statusTerbaru, 'setuju') ? 'lb-hijau'
        : ((str_contains($statusTerbaru, 'tolak') || str_contains($statusTerbaru, 'kembali')) ? 'lb-merah' : 'lb-oranye');
@endphp

<style>
.lembur-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 8px 24px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.lembur-page * {
    box-sizing: border-box;
}
.lb-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
    padding: 22px 24px;
    margin-bottom: 20px;
}
.lb-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}
.lb-title {
    font-size: 19px;
    font-weight: 600;
    margin: 0;
    color: #111827;
}
.lb-title a {
    color: inherit;
    text-decoration: none;
}
.lb-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 14px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}
.lb-oranye {
    color: #9a4a00;
    background: rgba(255, 207, 172, 0.5);
}
.lb-biru {
    color: #1d4ed8;
    background: #dbeafe;
}
.lb-hijau {
    color: #16834f;
    background: #e6f4e9;
}
.lb-merah {
    color: #c62828;
    background: rgba(234, 74, 76, 0.12);
}

/* Stepper 4 tahap */
.lb-scroll {
    overflow-x: auto;
}
.lb-steps {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    min-width: 560px;
}
.lb-step {
    position: relative;
    text-align: center;
    min-width: 0;
}
.lb-step:not(:last-child)::after {
    content: "";
    position: absolute;
    top: 15px;
    left: calc(50% + 16px);
    width: calc(100% - 32px);
    height: 2px;
    background: #d8dee7;
}
.lb-step.completed:not(:last-child)::after {
    background: #ff941c;
}
.lb-circle {
    position: relative;
    z-index: 1;
    width: 30px;
    height: 30px;
    margin: 0 auto 10px;
    border: 2px solid #acb1b9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #6b7280;
    font-size: 13px;
    font-weight: 600;
}
.lb-step.completed .lb-circle {
    background: #ff941c;
    border-color: #ff941c;
    color: #fff;
}
.lb-step.active .lb-circle {
    border-color: #ff941c;
    color: #ff941c;
}
.lb-step-name {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
}
.lb-step-role {
    font-size: 12px;
    color: #6b7280;
    margin-top: 3px;
}

/* Tabel daftar rencana lembur */
.lb-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
}
.lb-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 720px;
}
.lb-table thead {
    background: #eef2f6;
}
.lb-table th {
    height: 46px;
    padding: 0 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
}
.lb-table td {
    height: 56px;
    padding: 0 16px;
    border-top: 1px solid #e5e7eb;
    font-size: 14px;
    color: #111827;
    white-space: nowrap;
    vertical-align: middle;
}
.lb-table tbody tr:hover {
    background: #fafafa;
}
.btn-lb-detail {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 32px;
    padding: 0 14px;
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    background: #fff;
    color: #111827;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s ease;
}
.btn-lb-detail:hover {
    border-color: #ff941c;
    color: #ff941c;
    background: #fffaf4;
}
.lb-empty {
    text-align: center !important;
    padding: 32px 16px !important;
    color: #6b7280 !important;
    font-size: 14px;
}
.lb-pagination {
    margin-top: 16px;
}
</style>

<div class="lembur-page">
    @if ($terbaru)
        <section class="lb-card">
            <div class="lb-head">
                <h2 class="lb-title">
                    <a href="{{ route('saya.lembur.show', $terbaru) }}">
                        Rencana Lembur Terbaru · {{ $terbaru->no_pengajuan }}
                    </a>
                </h2>
                <span class="lb-badge {{ $badgeTerbaru }}">{{ $terbaru->status_rencana }}</span>
            </div>

            <div class="lb-scroll">
                <div class="lb-steps">
                    @foreach ($tahapLembur as $i => $tahap)
                        @php
                            $nomor = $i + 1;
                            [$namaTahap, $peranTahap] = $tahap;
                            $selesai = $nomor <= $langkahAktif;
                            $aktif = ! $selesai && $nomor === $langkahAktif + 1;
                        @endphp
                        <div class="lb-step {{ $selesai ? 'completed' : ($aktif ? 'active' : '') }}">
                            <div class="lb-circle">{!! $selesai ? '✓' : $nomor !!}</div>
                            <div class="lb-step-name">{{ $namaTahap }}</div>
                            <div class="lb-step-role">{{ $peranTahap }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="lb-card">
        <div class="lb-head" style="margin-bottom: 16px;">
            <h2 class="lb-title">Daftar rencana lembur saya</h2>
        </div>

        <div class="lb-table-wrap">
            <table class="lb-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>No. Pengajuan</th>
                        <th>Tanggal</th>
                        <th>Jam Lembur</th>
                        <th>Pekerjaan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $r)
                        @php
                            $milik = $r->detail->firstWhere('id_karyawan', $saya->id_karyawan);
                            $st = strtolower((string) ($r->status_rencana ?? ''));
                            $badgeClass = str_contains($st, 'setuju') ? 'lb-hijau'
                                : ((str_contains($st, 'tolak') || str_contains($st, 'kembali')) ? 'lb-merah'
                                : (str_contains($st, 'general manager') ? 'lb-biru' : 'lb-oranye'));
                            $noUrut = method_exists($daftar, 'firstItem') && $daftar->firstItem()
                                ? $daftar->firstItem() + $loop->index
                                : $loop->iteration;
                        @endphp
                        <tr>
                            <td>{{ $noUrut }}</td>
                            <td><strong>{{ $r->no_pengajuan }}</strong></td>
                            <td>{{ \App\Support\Format::tanggal($r->tanggal_lembur) }}</td>
                            <td>{{ \App\Support\Format::rentangJam($r->jam_mulai, $r->jam_selesai) }}</td>
                            <td>{{ $milik?->pekerjaan ?? '—' }}</td>
                            <td>
                                <span class="lb-badge {{ $badgeClass }}">{{ $r->status_rencana }}</span>
                            </td>
                            <td>
                                <a href="{{ route('saya.lembur.show', $r) }}" class="btn-lb-detail">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="lb-empty">Anda belum tercatat dalam rencana lembur.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($daftar, 'links'))
            <div class="lb-pagination">
                {{ $daftar->links() }}
            </div>
        @endif
    </section>
</div>
@endsection