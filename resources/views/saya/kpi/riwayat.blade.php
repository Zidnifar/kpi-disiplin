@extends('layouts.app')

@section('title', 'Riwayat KPI')
@section('judul', 'Riwayat KPI')

@section('isi')
@php
    $fmt = fn ($v) => \App\Support\Format::nilai($v);

    $daftarTriwulan = collect($semua ?? [])->values();
    $daftarSemester = collect($semester ?? [])->values();
    $jumlahBar = max(1, $daftarTriwulan->count());
    $indeksTerakhir = $daftarTriwulan->count() - 1;
@endphp

<style>
.riwayat-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 8px 28px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.riwayat-page * {
    box-sizing: border-box;
}
.rw-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
    padding: 22px 24px;
    margin-bottom: 20px;
}
.rw-card:last-child {
    margin-bottom: 0;
}
.rw-title {
    margin: 0 0 18px;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}
.rw-muted {
    margin: 0;
    font-size: 13.5px;
    color: #6b7280;
}

/* Grafik Batang Vertikal Riwayat Triwulan */
.rw-chart-scroll {
    width: 100%;
    overflow-x: auto;
}
.rw-chart-inner {
    min-width: 640px;
}
.rw-plot {
    position: relative;
    height: 195px;
    margin: 28px 0 0 38px;
    border-bottom: 1.5px solid #cbd5e1;
}
.rw-grid-line {
    position: absolute;
    left: 0;
    right: 0;
    border-top: 1px solid #eef0f4;
}
.rw-y-label {
    position: absolute;
    left: -38px;
    width: 30px;
    text-align: right;
    font-size: 12px;
    color: #6b7280;
    transform: translateY(-50%);
}
.rw-bars {
    position: absolute;
    inset: 0;
    display: grid;
    gap: 12px;
    padding: 0 10px;
}
.rw-bar-col {
    position: relative;
    height: 100%;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}
.rw-bar-val {
    position: absolute;
    font-size: 12px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
}
.rw-bar {
    width: min(44px, 68%);
    background: #fdd2a0;
    border-radius: 8px 8px 4px 4px;
}
.rw-bar.terakhir {
    background: #ff941c;
}
.rw-x-labels {
    display: grid;
    gap: 12px;
    padding: 0 10px;
    margin: 8px 0 0 38px;
    text-align: center;
    font-size: 12px;
    color: #6b7280;
}
.rw-x-labels span {
    white-space: nowrap;
}

/* Tabel Riwayat per Semester */
.rw-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
}
.rw-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 820px;
}
.rw-table thead {
    background: #eef2f6;
}
.rw-table th {
    height: 44px;
    padding: 0 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
}
.rw-table td {
    height: 54px;
    padding: 0 16px;
    border-top: 1px solid #e5e7eb;
    font-size: 14px;
    color: #111827;
    vertical-align: middle;
}
.rw-table th.kanan,
.rw-table td.kanan {
    text-align: right;
}
.rw-table tbody tr:hover {
    background: #fafafa;
}

/* Badge Kategori */
.rw-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}
.rw-hijau {
    background: #e6f4e9;
    color: #16834f;
}
.rw-biru {
    background: #dbeafe;
    color: #1d4ed8;
}
.rw-oranye {
    background: rgba(255, 207, 172, 0.55);
    color: #9a4a00;
}
.rw-merah {
    background: rgba(234, 74, 76, 0.12);
    color: #c62828;
}

/* Tombol Aksi */
.rw-aksi-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.btn-rw-detail {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 34px;
    padding: 0 14px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    background: #fff;
    color: #111827;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.15s ease;
}
.btn-rw-detail:hover {
    border-color: #ff941c;
    color: #ff941c;
    background: #fffaf4;
}
.rw-empty {
    text-align: center !important;
    padding: 32px 16px !important;
    color: #6b7280 !important;
    font-size: 14px;
}
</style>

<div class="riwayat-page">
    <!-- Kartu Grafik Riwayat Nilai Triwulan -->
    <section class="rw-card">
        <h2 class="rw-title">Riwayat Nilai Triwulan</h2>

        @if ($daftarTriwulan->isNotEmpty())
            <div class="rw-chart-scroll">
                <div class="rw-chart-inner">
                    <div class="rw-plot">
                        @foreach ([0, 25, 50, 75, 100] as $y)
                            @if ($y > 0)
                                <div class="rw-grid-line" style="bottom: {{ $y }}%;"></div>
                            @endif
                            <span class="rw-y-label" style="bottom: {{ $y }}%;">{{ $y }}</span>
                        @endforeach

                        <div class="rw-bars" style="grid-template-columns: repeat({{ $jumlahBar }}, minmax(0, 1fr));">
                            @foreach ($daftarTriwulan as $idx => $h)
                                @php
                                    $nilai = (float) ($h->nilai_triwulan ?? 0);
                                    $tinggi = max(0, min(100, $nilai));
                                    $isTerakhir = $idx === $indeksTerakhir;
                                @endphp
                                <div class="rw-bar-col">
                                    <span class="rw-bar-val" style="bottom: calc({{ $tinggi }}% + 6px);">{{ $fmt($nilai) }}</span>
                                    <div class="rw-bar {{ $isTerakhir ? 'terakhir' : '' }}" style="height: {{ $tinggi }}%;"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rw-x-labels" style="grid-template-columns: repeat({{ $jumlahBar }}, minmax(0, 1fr));">
                        @foreach ($daftarTriwulan as $h)
                            <span>TW{{ $h->periode?->triwulan }} {{ $h->periode?->tahun }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <p class="rw-muted">Belum ada hasil KPI yang divalidasi.</p>
        @endif
    </section>

    <!-- Kartu Tabel Riwayat Hasil KPI per Semester -->
    <section class="rw-card">
        <h2 class="rw-title">Riwayat hasil KPI per semester</h2>

        <div class="rw-table-wrap">
            <table class="rw-table">
                <thead>
                    <tr>
                        <th>Semester</th>
                        <th class="kanan">Triwulan 1</th>
                        <th class="kanan">Triwulan 2</th>
                        <th class="kanan">Triwulan 3</th>
                        <th class="kanan">Triwulan 4</th>
                        <th class="kanan">Nilai Semester</th>
                        <th>Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarSemester as $s)
                        @php
                            $namaPred = $s->predikat?->nama_predikat;
                            $predLower = strtolower((string) $namaPred);
                            $badgeClass = str_contains($predLower, 'cukup') ? 'rw-oranye'
                                : ((str_contains($predLower, 'kurang') || str_contains($predLower, 'buruk')) ? 'rw-merah'
                                : (str_contains($predLower, 'sangat') ? 'rw-hijau' : 'rw-biru'));

                            $twPertama = $s->hasilTwPertama;
                            $twKedua = $s->hasilTwKedua;
                            $noTwPertama = (int) ($twPertama?->periode?->triwulan ?? 0);
                            $noTwKedua = (int) ($twKedua?->periode?->triwulan ?? 0);

                            $nilaiTw1 = $noTwPertama === 1 ? $twPertama?->nilai_triwulan : null;
                            $nilaiTw2 = $noTwKedua === 2 ? $twKedua?->nilai_triwulan : null;
                            $nilaiTw3 = $noTwPertama === 3 ? $twPertama?->nilai_triwulan : null;
                            $nilaiTw4 = $noTwKedua === 4 ? $twKedua?->nilai_triwulan : null;
                        @endphp
                        <tr>
                            <td>Semester {{ $s->semester }} {{ $s->tahun }}</td>
                            <td class="kanan">{{ $fmt($nilaiTw1) }}</td>
                            <td class="kanan">{{ $fmt($nilaiTw2) }}</td>
                            <td class="kanan">{{ $fmt($nilaiTw3) }}</td>
                            <td class="kanan">{{ $fmt($nilaiTw4) }}</td>
                            <td class="kanan"><strong>{{ $fmt($s->nilai_semester) }}</strong></td>
                            <td>
                                @if ($namaPred)
                                    <span class="rw-badge {{ $badgeClass }}">{{ $namaPred }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="rw-aksi-group">
                                    @if ($twPertama?->id_periode)
                                        <a href="{{ route('saya.kpi.rincian', ['periode' => $twPertama->id_periode]) }}" class="btn-rw-detail">
                                            Rincian TW{{ $noTwPertama }}
                                        </a>
                                    @endif
                                    @if ($twKedua?->id_periode)
                                        <a href="{{ route('saya.kpi.rincian', ['periode' => $twKedua->id_periode]) }}" class="btn-rw-detail">
                                            Rincian TW{{ $noTwKedua }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="rw-empty">Belum ada nilai semester.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection