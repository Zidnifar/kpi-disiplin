@extends('layouts.app')

@section('title', 'Dashboard Kepala Unit')
@section('judul', 'Dashboard')

@section('isi')
@php
    $daftarKartu = collect($kartu ?? []);
    $daftarTugas = collect(array_filter($tugas ?? [], fn ($t) => ($t['jumlah'] ?? 0) > 0));
    $periodeKpi  = $extra['periode'] ?? null;
    $ringkasanUnit = $extra['ringkasan'] ?? null;
@endphp

<div class="ku-dash">
    <section class="ku-stats-grid">
        @foreach ($daftarKartu as $item)
            <div class="ku-card ku-stat-card">
                <div>
                    <div class="ku-stat-num">{{ $item['nilai'] ?? 0 }}</div>
                    <div class="ku-stat-lbl">{{ $item['label'] ?? '-' }}</div>
                </div>
                <div class="ku-stat-icon">
                    @switch($item['ikon'] ?? '')
                        @case('users')
                            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            @break
                        @case('checkc')
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>
                            @break
                        @case('chart')
                            <svg viewBox="0 0 24 24"><path d="M4 20V14"/><path d="M12 20V9"/><path d="M20 20V4"/></svg>
                            @break
                        @default
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg>
                    @endswitch
                </div>
            </div>
        @endforeach
    </section>

    <section class="ku-main-grid">
        <div class="ku-card ku-panel">
            <h2 class="ku-panel-title">Perlu ditindaklanjuti</h2>
            @if ($daftarTugas->isNotEmpty())
                <div class="ku-task-list">
                    @foreach ($daftarTugas as $t)
                        <a href="{{ $t['url'] ?? '#' }}" class="ku-task-item">
                            <div class="ku-task-left">
                                <span class="ku-task-badge">{{ $t['jumlah'] }}</span>
                                <span class="ku-task-text">{{ $t['teks'] }}</span>
                            </div>
                            <span class="ku-task-link">Buka →</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="ku-empty">Tidak ada tugas yang perlu ditindaklanjuti saat ini.</div>
            @endif
        </div>

        <div class="ku-card ku-panel">
            <h2 class="ku-panel-title">Ringkasan Unit</h2>
            <div class="ku-info-rows">
                <div class="ku-info-row">
                    <span>Unit</span>
                    <strong>{{ $pengguna->karyawan->departemen->nama_dept ?? '—' }}</strong>
                </div>
                <div class="ku-info-row">
                    <span>Periode Penilaian</span>
                    <strong>{{ $periodeKpi->nama_periode ?? 'Belum ada periode' }}</strong>
                </div>
                @if (is_array($ringkasanUnit) && isset($ringkasanUnit['rata']))
                    <div class="ku-info-row">
                        <span>Rata-rata KPI Unit</span>
                        <strong>{{ \App\Support\Format::nilai($ringkasanUnit['rata']) }}</strong>
                    </div>
                @endif
            </div>

            <div class="ku-actions">
                @if (\Illuminate\Support\Facades\Route::has('unit.lembur.index'))
                    <a href="{{ route('unit.lembur.index') }}" class="ku-btn-oren">Rencana Lembur</a>
                @endif
                @if (\Illuminate\Support\Facades\Route::has('unit.realisasi.index'))
                    <a href="{{ route('unit.realisasi.index') }}" class="ku-btn-sekunder">Realisasi Lembur</a>
                @endif
                @if (\Illuminate\Support\Facades\Route::has('unit.kpi.pemeriksaan'))
                    <a href="{{ route('unit.kpi.pemeriksaan') }}" class="ku-btn-sekunder">Pemeriksaan KPI</a>
                @endif
            </div>
        </div>
    </section>
</div>

<style>
.ku-dash {
    width: 100%;
    padding: 0 8px 28px;
    color: #101828;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.ku-dash * { box-sizing: border-box; }
.ku-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
}
.ku-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 20px;
}
.ku-stat-card {
    min-height: 98px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}
.ku-stat-num {
    font-size: clamp(24px, 2.2vw, 32px);
    font-weight: 700;
    line-height: 1.15;
    margin-bottom: 6px;
}
.ku-stat-lbl { font-size: 13px; color: #475467; }
.ku-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: #eef2f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ku-stat-icon svg {
    width: 21px;
    height: 21px;
    stroke: #101828;
    stroke-width: 1.8;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.ku-main-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}
.ku-panel { padding: 22px 24px; }
.ku-panel-title {
    margin: 0 0 16px;
    font-size: 18px;
    font-weight: 700;
}
.ku-task-list { display: flex; flex-direction: column; gap: 10px; }
.ku-task-item {
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
.ku-task-item:hover { border-color: #ff941c; background: #fffaf5; }
.ku-task-left { display: flex; align-items: center; gap: 12px; }
.ku-task-badge {
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
.ku-task-text { font-size: 13.5px; font-weight: 500; }
.ku-task-link { font-size: 13px; font-weight: 600; color: #ff941c; white-space: nowrap; }
.ku-info-rows { display: flex; flex-direction: column; gap: 12px; margin-bottom: 18px; }
.ku-info-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f2f4f7;
    font-size: 13.5px;
}
.ku-info-row span { color: #667085; }
.ku-actions { display: flex; flex-wrap: wrap; gap: 10px; }
.ku-btn-oren, .ku-btn-sekunder {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 38px;
    padding: 0 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}
.ku-btn-oren { background: #ff941c; color: #fff; }
.ku-btn-oren:hover { background: #e8850f; }
.ku-btn-sekunder { border: 1px solid #d0d5dd; background: #fff; color: #101828; }
.ku-btn-sekunder:hover { background: #f8fafc; }
.ku-empty { font-size: 13.5px; color: #667085; padding: 8px 0; }
@media (max-width: 1100px) { .ku-stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 768px) { .ku-main-grid { grid-template-columns: 1fr; } }
@media (max-width: 560px) { .ku-stats-grid { grid-template-columns: 1fr; } }
</style>
@endsection