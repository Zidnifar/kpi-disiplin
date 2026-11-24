@extends('layouts.app')

@section('title', 'Info Rencana Lembur')
@section('judul', 'Info Rencana Lembur')

@section('isi')
@php
    $riwayatPersetujuan = method_exists($rencana, 'persetujuan') ? $rencana->persetujuan : collect();

    $logPeriksa = $riwayatPersetujuan->firstWhere('tahap', 'periksa');
    $logKetahui = $riwayatPersetujuan->firstWhere('tahap', 'ketahui');
    $logKeputusan = $riwayatPersetujuan->firstWhere('tahap', 'putuskan')
        ?? $riwayatPersetujuan->firstWhere('tahap', 'keputusan');

    $namaPengajuSingkat = $rencana->pengaju->nama_lengkap
        ?? $rencana->pengaju->nama
        ?? $rencana->pengaju->karyawan->nama_lengkap
        ?? 'Pengaju';

    $tglAjukan = $rencana->created_at
        ? \App\Support\Format::tanggalPendek($rencana->created_at)
        : \App\Support\Format::tanggalPendek($rencana->tanggal_lembur);

    $namaPemeriksa = $logPeriksa?->pengguna?->karyawan?->nama_lengkap
        ?? $logPeriksa?->pengguna?->nama_lengkap
        ?? $logPeriksa?->pengguna?->nama
        ?? 'Ka. Produksi';

    $tglPeriksa = $logPeriksa?->created_at
        ? \App\Support\Format::tanggalPendek($logPeriksa->created_at)
        : 'Selesai';

    $catatanProduksi = $logPeriksa?->catatan
        ?? $rencana->catatan_pemeriksaan
        ?? $rencana->catatan_produksi
        ?? null;

    $statusSaatIni = (string) ($rencana->status_rencana ?? '');
    $sudahMelewatiKetahui = $logKetahui !== null
        || in_array($statusSaatIni, [
            \App\Models\RencanaLembur::MENUNGGU_GM,
            \App\Models\RencanaLembur::DISETUJUI,
            \App\Models\RencanaLembur::DITOLAK,
            \App\Models\RencanaLembur::SELESAI,
        ], true);

    $teksLangkah3 = $sudahMelewatiKetahui
        ? ($logKetahui?->pengguna?->karyawan?->nama_lengkap ?? $logKetahui?->pengguna?->nama ?? 'Sudah Diketahui')
        : 'Menunggu Opr. Manager';

    $sudahDiputuskan = $logKeputusan !== null
        || in_array($statusSaatIni, [
            \App\Models\RencanaLembur::DISETUJUI,
            \App\Models\RencanaLembur::DITOLAK,
            \App\Models\RencanaLembur::SELESAI,
        ], true);

    $teksLangkah4 = match ($statusSaatIni) {
        \App\Models\RencanaLembur::DISETUJUI => 'Disetujui GM',
        \App\Models\RencanaLembur::SELESAI   => 'Selesai',
        \App\Models\RencanaLembur::DITOLAK   => 'Ditolak GM',
        default                               => 'General Manager',
    };

    $bisaTandaiDiketahui = $statusSaatIni === \App\Models\RencanaLembur::MENUNGGU_OPERASIONAL;
@endphp

<div class="periksa-lembur-page">
    <nav class="pl-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('lembur.ketahui.index') }}">Info Rencana Lembur</a>
        <span>/</span>
        <strong>Detail · {{ $rencana->no_pengajuan }}</strong>
    </nav>

    @include('lembur._detail', ['rencana' => $rencana])

    <!-- Kartu Alur Persetujuan -->
    <div class="pl-card pl-detail-card">
        <h2 class="pl-detail-title" style="margin-bottom: 18px;">Alur Persetujuan</h2>

        <div class="pl-stepper">
            <!-- Langkah 1: Diajukan -->
            <div class="pl-step done">
                <div class="pl-step-circle">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div class="pl-step-title">Diajukan</div>
                <div class="pl-step-sub">{{ $namaPengajuSingkat }} · {{ $tglAjukan }}</div>
            </div>

            <!-- Langkah 2: Diperiksa -->
            <div class="pl-step done">
                <div class="pl-step-circle">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div class="pl-step-title">Diperiksa</div>
                <div class="pl-step-sub">{{ $namaPemeriksa }} · {{ $tglPeriksa }}</div>
            </div>

            <!-- Langkah 3: Diketahui -->
            <div class="pl-step {{ $sudahMelewatiKetahui ? 'done' : 'current' }}">
                <div class="pl-step-circle">
                    @if ($sudahMelewatiKetahui)
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                        <span>3</span>
                    @endif
                </div>
                <div class="pl-step-title">Diketahui</div>
                <div class="pl-step-sub">{{ $teksLangkah3 }}</div>
            </div>

            <!-- Langkah 4: Keputusan -->
            <div class="pl-step {{ $sudahDiputuskan ? 'done' : 'pending' }}">
                <div class="pl-step-circle">
                    @if ($sudahDiputuskan)
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                        <span>4</span>
                    @endif
                </div>
                <div class="pl-step-title">Keputusan</div>
                <div class="pl-step-sub">{{ $teksLangkah4 }}</div>
            </div>
        </div>
    </div>

    <!-- Kartu Catatan Kepala Produksi -->
    @if (filled($catatanProduksi))
        <div class="pl-card pl-detail-card">
            <div class="pl-note-box">
                <div class="pl-note-icon">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>
                </div>
                <div>
                    <div class="pl-note-title">Catatan Kepala Produksi</div>
                    <div class="pl-note-text">{{ $catatanProduksi }}</div>
                </div>
            </div>
        </div>
    @endif

    @if ($bisaTandaiDiketahui && \Illuminate\Support\Facades\Route::has('lembur.ketahui.ketahui'))
        <form method="POST" action="{{ route('lembur.ketahui.ketahui', $rencana) }}">
            @csrf
            <div class="pl-form-actions">
                <a href="{{ route('lembur.ketahui.index') }}" class="pl-btn pl-btn-sekunder">
                    <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Kembali
                </a>
                <button type="submit" class="pl-btn pl-btn-oren">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Tandai Sudah Diketahui
                </button>
            </div>
        </form>
    @else
        <div class="pl-form-actions">
            <a href="{{ route('lembur.ketahui.index') }}" class="pl-btn pl-btn-sekunder">
                <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Kembali
            </a>
        </div>
    @endif
</div>

@include('lembur._gaya')
@endsection