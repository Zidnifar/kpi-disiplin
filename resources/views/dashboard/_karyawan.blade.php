@extends('layouts.app')

@section('title', 'Dashboard Karyawan')
@section('judul', 'Dashboard')

@section('isi')
@php
    $daftarKartu = collect($kartu ?? []);
    $daftarTugas = collect(array_filter($tugas ?? [], fn ($t) => ($t['jumlah'] ?? 0) > 0));
    $hasilKpi    = $extra['hasil'] ?? null;
    $lemburTerakhir = $extra['lembur'] ?? null;
@endphp

<div class="kry-dash">
    <section class="kry-stats-grid">
        @foreach ($daftarKartu as $item)
            <div class="kry-card kry-stat-card">
                <div>
                    <div class="kry-stat-num">{{ $item['nilai'] ?? 0 }}</div>
                    <div class="kry-stat-lbl">{{ $item['label'] ?? '-' }}</div>
                </div>
                <div class="kry-stat-icon">
                    @switch($item['ikon'] ?? '')
                        @case('checkc')
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>
                            @break
                        @case('alert')
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            @break
                        @case('award')
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                            @break
                        @default
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg>
                    @endswitch
                </div>
            </div>
        @endforeach
    </section>

    @if ($daftarTugas->isNotEmpty())
        <section class="kry-card kry-panel" style="margin-bottom: 20px;">
            <h2 class="kry-panel-title">Perlu ditindaklanjuti</h2>
            <div class="kry-task-list">
                @foreach ($daftarTugas as $t)
                    <a href="{{ $t['url'] ?? '#' }}" class="kry-task-item">
                        <div class="kry-task-left">
                            <span class="kry-task-badge">{{ $t['jumlah'] }}</span>
                            <span class="kry-task-text">{{ $t['teks'] }}</span>
                        </div>
                        <span class="kry-task-link">Buka →</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="kry-main-grid">
        <div class="kry-card kry-panel">
            <h2 class="kry-panel-title">Nilai KPI Terakhir</h2>
            @if ($hasilKpi)
                <div class="kry-info-rows">
                    <div class="kry-info-row">
                        <span>Periode</span>
                        <strong>{{ $hasilKpi->periode->nama_periode ?? '—' }}</strong>
                    </div>
                    <div class="kry-info-row">
                        <span>Nilai Triwulan</span>
                        <strong>{{ \App\Support\Format::nilai($hasilKpi->nilai_triwulan) }}</strong>
                    </div>
                </div>
            @else
                <div class="kry-empty">Belum ada nilai KPI yang tervalidasi.</div>
            @endif
        </div>

        <div class="kry-card kry-panel">
            <h2 class="kry-panel-title">Status Lembur Terakhir</h2>
            @if ($lemburTerakhir)
                <div class="kry-info-rows">
                    <div class="kry-info-row">
                        <span>No. Pengajuan</span>
                        <strong>{{ $lemburTerakhir->no_pengajuan }}</strong>
                    </div>
                    <div class="kry-info-row">
                        <span>Tanggal</span>
                        <strong>{{ \App\Support\Format::tanggal($lemburTerakhir->tanggal_lembur) }}</strong>
                    </div>
                    <div class="kry-info-row">
                        <span>Status</span>
                        <span class="kry-badge">{{ $lemburTerakhir->status_rencana }}</span>
                    </div>
                </div>
            @else
                <div class="kry-empty">Belum ada riwayat lembur.</div>
            @endif
        </div>
    </section>
</div>

<style>
.kry-dash {
    width: 100%;
    padding: 0 8px 28px;
    color: #101828;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.kry-dash * { box-sizing: border-box; }
.kry-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
}
.kry-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 20px;
}
.kry-stat-card {
    min-height: 98px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}
.kry-stat-num {
    font-size: clamp(24px, 2.2vw, 32px);
    font-weight: 700;
    line-height: 1.15;
    margin-bottom: 6px;
}
.kry-stat-lbl { font-size: 13px; color: #475467; }
.kry-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: #eef2f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.kry-stat-icon svg {
    width: 21px;
    height: 21px;
    stroke: #101828;
    stroke-width: 1.8;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.kry-main-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}
.kry-panel { padding: 22px 24px; }
.kry-panel-title {
    margin: 0 0 16px;
    font-size: 18px;
    font-weight: 700;
}
.kry-task-list { display: flex; flex-direction: column; gap: 10px; }
.kry-task-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    border: 1px solid #e4e7ec;
    border-radius: 12px;
    text-decoration: none;
    color: #101828;
}
.kry-task-item:hover { border-color: #ff941c; background: #fffaf5; }
.kry-task-left { display: flex; align-items: center; gap: 12px; }
.kry-task-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    padding: 0 8px;
    border-radius: 8px;
    background: #ffedd5;
    color: #c2410c;
    font-size: 13px;
    font-weight: 700;
}
.kry-task-text { font-size: 13.5px; font-weight: 500; }
.kry-task-link { font-size: 13px; font-weight: 600; color: #ff941c; white-space: nowrap; }
.kry-info-rows { display: flex; flex-direction: column; gap: 12px; }
.kry-info-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f2f4f7;
    font-size: 13.5px;
}
.kry-info-row:last-child { border-bottom: 0; padding-bottom: 0; }
.kry-info-row span { color: #667085; }
.kry-badge {
    display: inline-flex;
    align-items: center;
    height: 28px;
    padding: 0 12px;
    border-radius: 8px;
    background: #ffedd5;
    color: #c2410c;
    font-size: 12px;
    font-weight: 600;
}
.kry-empty { font-size: 13.5px; color: #667085; padding: 8px 0; }
@media (max-width: 1100px) { .kry-stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 768px) { .kry-main-grid { grid-template-columns: 1fr; } }
@media (max-width: 560px) { .kry-stats-grid { grid-template-columns: 1fr; } }
</style>
@endsection