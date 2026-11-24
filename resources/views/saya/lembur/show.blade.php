@extends('layouts.app')

@section('title', 'Detail Lembur')
@section('judul', 'Detail Lembur')

@section('isi')
@php
    $tahapLembur = [
        ['Diajukan', 'Kepala Unit'],
        ['Diperiksa', 'Kepala Produksi'],
        ['Diketahui', 'Operasional Manager'],
        ['Keputusan', 'General Manager'],
    ];

    $langkahAktif = \App\Services\DashboardKaryawanService::langkahLembur($rencana->status_rencana);
    $st = strtolower((string) ($rencana->status_rencana ?? ''));
    $badgeClass = str_contains($st, 'setuju') ? 'lb-hijau'
        : ((str_contains($st, 'tolak') || str_contains($st, 'kembali')) ? 'lb-merah'
        : (str_contains($st, 'general manager') ? 'lb-biru' : 'lb-oranye'));

    $idKaryawanLogin = auth()->user()?->karyawan?->id_karyawan ?? ($saya->id_karyawan ?? null);
    $detailMilik = $idKaryawanLogin ? $rencana->detail->firstWhere('id_karyawan', $idKaryawanLogin) : null;
@endphp

<style>
.lembur-detail-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 8px 24px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.lembur-detail-page * {
    box-sizing: border-box;
}
.ld-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 16px;
}
.ld-breadcrumb a {
    color: #6b7280;
    text-decoration: none;
}
.ld-breadcrumb a:hover {
    color: #111827;
}
.ld-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
    padding: 22px 24px;
    margin-bottom: 20px;
}
.ld-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.ld-title {
    font-size: 19px;
    font-weight: 600;
    margin: 0 0 4px;
    color: #111827;
}
.ld-sub {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
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

/* Stepper */
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

/* Grid informasi pokok */
.ld-info-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}
.ld-info-box {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 14px 16px;
    background: #f9fafb;
}
.ld-info-label {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 5px;
}
.ld-info-val {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

/* Tabel rincian */
.lb-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
}
.lb-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 560px;
}
.lb-table thead {
    background: #eef2f6;
}
.lb-table th {
    height: 44px;
    padding: 0 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
}
.lb-table td {
    height: 52px;
    padding: 0 16px;
    border-top: 1px solid #e5e7eb;
    font-size: 14px;
    color: #111827;
}
.ld-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 18px;
}
.btn-ld-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 38px;
    padding: 0 18px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    background: #fff;
    color: #111827;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s ease;
}
.btn-ld-back:hover {
    border-color: #9ca3af;
    background: #f9fafb;
}

@media (max-width: 900px) {
    .ld-info-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 560px) {
    .ld-info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="lembur-detail-page">
    <div class="ld-breadcrumb">
        <a href="{{ route('saya.lembur') }}">Status Lembur</a>
        <span>/</span>
        <strong style="color: #111827;">{{ $rencana->no_pengajuan }}</strong>
    </div>

    <section class="ld-card">
        <div class="ld-head">
            <div>
                <h2 class="ld-title">Rencana Lembur · {{ $rencana->no_pengajuan }}</h2>
                <p class="ld-sub">
                    {{ \App\Support\Format::tanggal($rencana->tanggal_lembur) }} · {{ \App\Support\Format::rentangJam($rencana->jam_mulai, $rencana->jam_selesai) }}
                </p>
            </div>
            <span class="lb-badge {{ $badgeClass }}">{{ $rencana->status_rencana }}</span>
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

    <section class="ld-card">
        <div class="ld-head" style="margin-bottom: 16px;">
            <h2 class="ld-title" style="margin: 0;">Rincian Pengajuan Lembur</h2>
        </div>

        <div class="ld-info-grid">
            <div class="ld-info-box">
                <div class="ld-info-label">No. Pengajuan</div>
                <div class="ld-info-val">{{ $rencana->no_pengajuan }}</div>
            </div>
            <div class="ld-info-box">
                <div class="ld-info-label">Tanggal Lembur</div>
                <div class="ld-info-val">{{ \App\Support\Format::tanggal($rencana->tanggal_lembur) }}</div>
            </div>
            <div class="ld-info-box">
                <div class="ld-info-label">Jam Lembur</div>
                <div class="ld-info-val">{{ \App\Support\Format::rentangJam($rencana->jam_mulai, $rencana->jam_selesai) }}</div>
            </div>
            <div class="ld-info-box">
                <div class="ld-info-label">Pekerjaan Saya</div>
                <div class="ld-info-val">{{ $detailMilik?->pekerjaan ?? '—' }}</div>
            </div>
        </div>

        @if (!empty($rencana->catatan) || !empty($rencana->alasan_penolakan))
            <div class="ld-info-box" style="margin-bottom: 20px; background: #fff;">
                <div class="ld-info-label">{{ !empty($rencana->alasan_penolakan) ? 'Catatan / Alasan Penolakan' : 'Catatan' }}</div>
                <div class="ld-info-val" style="font-weight: 500;">{{ $rencana->alasan_penolakan ?? $rencana->catatan }}</div>
            </div>
        @endif

        <div class="lb-table-wrap">
            <table class="lb-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Karyawan</th>
                        <th>Pekerjaan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rencana->detail as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $item->karyawan?->nama_karyawan ?? '—' }}</strong></td>
                            <td>{{ $item->pekerjaan ?? '—' }}</td>
                            <td>{{ $item->keterangan ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #6b7280; padding: 24px;">Belum ada rincian tugas lembur.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ld-footer">
            <a href="{{ route('saya.lembur') }}" class="btn-ld-back">Kembali</a>
        </div>
    </section>
</div>
@endsection