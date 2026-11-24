@extends('layouts.app')

@section('judul', 'Detail Rencana Lembur')

@section('isi')
@php
    $status = (string) $rencana->status_rencana;
    $s = strtolower($status);

    $kelasStatus = match (true) {
        str_contains($s, 'tolak') => 'sw-merah',
        str_contains($s, 'setuju') || str_contains($s, 'selesai') => 'sw-hijau',
        str_contains($s, 'kembali') => 'sw-kuning',
        str_contains($s, 'produksi') => 'sw-oranye',
        str_contains($s, 'operasional') || str_contains($s, 'general') => 'sw-biru',
        default => 'sw-abu',
    };

    $langkah = str_contains($s, 'kembali') ? 0 : \App\Services\DashboardKaryawanService::langkahLembur($status);
    $ditolak = str_contains($s, 'tolak');
    $tahap = [
        ['Diajukan', 'Kepala Unit'],
        ['Diperiksa', 'Kepala Produksi'],
        ['Diketahui', 'Operasional Manager'],
        ['Keputusan', 'General Manager'],
    ];
@endphp

<div class="sw-page">
    <div class="sw-breadcrumb">
        <a href="{{ route('unit.lembur.index') }}">Rencana Lembur</a>
        <span>/</span>
        <strong>{{ $rencana->no_pengajuan }}</strong>
    </div>

    @if ($status === \App\Models\RencanaLembur::DIKEMBALIKAN)
        <div class="sw-alert sw-alert-kuning">Pengajuan dikembalikan. Perbaiki sesuai catatan di bawah, lalu ajukan lagi.</div>
    @elseif ($status === \App\Models\RencanaLembur::DITOLAK)
        <div class="sw-alert sw-alert-merah">Pengajuan ditolak General Manager. Buat pengajuan baru sesuai saran bila masih diperlukan.</div>
    @elseif ($status === \App\Models\RencanaLembur::DISETUJUI)
        <div class="sw-alert sw-alert-hijau">Pengajuan disetujui. Isi realisasi setelah lembur dilaksanakan melalui menu Realisasi Lembur.</div>
    @endif

    <!-- Ringkasan status & tahap persetujuan -->
    <div class="sw-card">
        <div class="sw-kepala">
            <div>
                <h2 class="sw-judul">Detail Rencana Lembur</h2>
                <div class="sw-sub">{{ $rencana->no_pengajuan }}</div>
            </div>
            <span class="sw-badge {{ $kelasStatus }}">{{ $status }}</span>
        </div>

        <div class="sw-stepper">
            @foreach ($tahap as $i => $t)
                @php
                    $n = $i + 1;
                    $kelas = $n <= $langkah ? 'selesai' : ($n === $langkah + 1 ? 'aktif' : '');
                    if ($ditolak && $n === 4) { $kelas = 'tolak'; }
                @endphp
                <div class="sw-step {{ $kelas }}">
                    <div class="sw-bulat">{!! $kelas === 'selesai' ? '✓' : ($kelas === 'tolak' ? '✕' : $n) !!}</div>
                    <div class="sw-nama">{{ $t[0] }}</div>
                    <div class="sw-peran">{{ $t[1] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Isi detail (tetap memakai partial yang sudah ada) -->
    @include('lembur._detail')

    <!-- Aksi -->
    <div class="sw-aksi">
        <a href="{{ route('unit.lembur.index') }}" class="sw-btn sw-btn-putih">Kembali</a>
        @if ($rencana->bolehDiubah())
            <a href="{{ route('unit.lembur.edit', $rencana) }}" class="sw-btn sw-btn-putih">Ubah</a>
            <form method="POST" action="{{ route('unit.lembur.ajukan', $rencana) }}" onsubmit="return confirm('Ajukan rencana lembur ini ke Kepala Produksi?');" style="margin: 0;">
                @csrf
                <button type="submit" class="sw-btn sw-btn-oranye">Ajukan Rencana</button>
            </form>
        @endif
        @if ($status === \App\Models\RencanaLembur::DISETUJUI)
            <a href="{{ route('unit.realisasi.edit', $rencana) }}" class="sw-btn sw-btn-oranye">Isi Realisasi</a>
        @endif
    </div>
</div>

<style>
.sw-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 8px 28px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI',Inter,Arial,sans-serif;
}

.sw-page * {
    box-sizing: border-box;
}

.sw-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 14px;
}

.sw-breadcrumb a {
    color: #6b7280;
    text-decoration: none;
}

.sw-breadcrumb a:hover {
    color: #ff941c;
}

.sw-breadcrumb strong {
    color: #111827;
    font-weight: 600;
}

.sw-alert {
    padding: 13px 18px;
    border-radius: 12px;
    font-size: 14px;
    margin-bottom: 16px;
}

.sw-alert-kuning {
    background: #fffbeb;
    border: 1px solid #fcd34d;
    color: #92400e;
}

.sw-alert-merah {
    background: #fef2f2;
    border: 1px solid #fca5a5;
    color: #991b1b;
}

.sw-alert-hijau {
    background: #ecfdf5;
    border: 1px solid #6ee7b7;
    color: #065f46;
}

.sw-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 22px;
    padding: 24px 26px;
    margin-bottom: 16px;
}

.sw-kepala {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}

.sw-judul {
    margin: 0;
    font-size: 20px;
    font-weight: 500;
}

.sw-sub {
    margin-top: 3px;
    font-size: 13px;
    color: #667085;
}

.sw-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 14px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}

.sw-abu {
    color: #475467;
    background: #eef0f3;
}

.sw-oranye {
    color: #9a4a00;
    background: rgba(255,207,172,.55);
}

.sw-biru {
    color: #1e56a0;
    background: #d6e9fb;
}

.sw-hijau {
    color: #16834f;
    background: #e6f4e9;
}

.sw-merah {
    color: #c62828;
    background: rgba(234,74,76,.1);
}

.sw-kuning {
    color: #b54708;
    background: #fef0c7;
}

.sw-stepper {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
}

.sw-step {
    position: relative;
    text-align: center;
    min-width: 0;
}

.sw-step:not(:last-child)::after {
    content: "";
    position: absolute;
    top: 15px;
    left: calc(50% + 16px);
    width: calc(100% - 32px);
    height: 2px;
    background: #d8dee7;
}

.sw-step.selesai:not(:last-child)::after {
    background: #ff941c;
}

.sw-bulat {
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
    font-size: 14px;
}

.sw-step.selesai .sw-bulat {
    background: #ff941c;
    border-color: #ff941c;
    color: #fff;
}

.sw-step.aktif .sw-bulat {
    border-color: #ff941c;
    color: #ff941c;
}

.sw-step.tolak .sw-bulat {
    background: #c62828;
    border-color: #c62828;
    color: #fff;
}

.sw-nama {
    font-size: 13px;
    font-weight: 500;
}

.sw-peran {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

.sw-aksi {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 18px;
}

.sw-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 44px;
    padding: 0 24px;
    border-radius: 11px;
    font-size: 14px;
    font-weight: 500;
    font-family: inherit;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background .2s,border-color .2s;
}

.sw-btn-putih {
    background: #fff;
    color: #111827;
    border-color: #d0d5dd;
}

.sw-btn-putih:hover {
    background: #f8fafc;
    border-color: #98a2b3;
}

.sw-btn-oranye {
    background: #ff941c;
    color: #fff;
}

.sw-btn-oranye:hover {
    background: #e58316;
}

@media(max-width:700px) {
    .sw-stepper {
        min-width: 520px;
    }

    .sw-card {
        overflow-x: auto;
    }
}
</style>
@endsection