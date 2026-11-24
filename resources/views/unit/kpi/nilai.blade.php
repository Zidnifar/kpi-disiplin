@extends('layouts.app')

@section('title', 'Nilai KPI Karyawan')
@section('judul', 'Nilai KPI Karyawan')

@section('isi')
@php
    $rawBaris = $baris ?? ($daftar ?? collect());
    $isPaginator = $rawBaris instanceof \Illuminate\Contracts\Pagination\Paginator
        || $rawBaris instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    $koleksiBaris = $isPaginator ? collect($rawBaris->items()) : collect($rawBaris);

    $dariData = $isPaginator
        ? ($rawBaris->total() > 0 ? ($rawBaris->firstItem() ?? 1) : 0)
        : ($koleksiBaris->count() > 0 ? 1 : 0);
    $sampaiData = $isPaginator
        ? ($rawBaris->lastItem() ?? $koleksiBaris->count())
        : $koleksiBaris->count();
    $totalDitampilkan = $isPaginator ? $rawBaris->total() : $koleksiBaris->count();

    $labelPeriode = (string) ($pilihan['label'] ?? '');
    preg_match('/\b(20\d{2})\b/', $labelPeriode, $cocokTahun);
    $tahunAktif = (string) ($tahun ?? request('tahun', $pilihan['tahun'] ?? ($cocokTahun[1] ?? now()->year)));

    $tahunOpsi = collect($tahunPilihan ?? [])
        ->map(fn ($t) => is_array($t) ? (string) ($t['nilai'] ?? $t['tahun'] ?? '') : (string) $t)
        ->filter()
        ->unique()
        ->values();

    if ($tahunOpsi->isEmpty()) {
        $dariSemester = collect($semesterPilihan ?? [])->map(function ($s) {
            $lbl = (string) ($s['label'] ?? '');
            preg_match('/\b(20\d{2})\b/', $lbl, $m);
            return (string) ($s['tahun'] ?? ($m[1] ?? ''));
        })->filter();

        $tahunOpsi = $dariSemester->isNotEmpty()
            ? $dariSemester->unique()->values()
            : collect([$tahunAktif]);
    }

    if ($tahunAktif !== '' && ! $tahunOpsi->contains($tahunAktif)) {
        $tahunOpsi->prepend($tahunAktif);
    }

    $rataRekap = $data['rata'] ?? null;
    $jumlahRekap = $data['jumlah'] ?? $totalDitampilkan;
    $tertinggiRekap = $data['tertinggi'] ?? null;
    $terendahRekap = $data['terendah'] ?? null;

    $kategoriUrut = ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'];
    $maksDistribusi = max(1, ...(array_map(fn ($k) => (int) ($sebaran[$k] ?? 0), $kategoriUrut) ?: [1]));

    $batangTriwulan = [
        ['Triwulan 1', $rataTw['tw1'] ?? null],
        ['Triwulan 2', $rataTw['tw2'] ?? null],
        ['Triwulan 3', $rataTw['tw3'] ?? null],
        ['Triwulan 4', $rataTw['tw4'] ?? null],
    ];

    $urlUnduhLaporan = \Illuminate\Support\Facades\Route::has('unit.laporan')
        ? route('unit.laporan', ['tahun' => $tahunAktif])
        : (\Illuminate\Support\Facades\Route::has('unit.kpi.nilai.excel')
            ? route('unit.kpi.nilai.excel', ['tahun' => $tahunAktif, 'semester' => $pilihan['nilai'] ?? null])
            : '#');
@endphp

<style>
.nk-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 8px 32px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.nk-page * {
    box-sizing: border-box;
}
.nk-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
}

/* 1. 4 Kartu Ringkasan Atas */
.nk-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}
.nk-stat-card {
    min-height: 98px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    min-width: 0;
}
.nk-stat-number {
    font-size: clamp(24px, 2.1vw, 32px);
    font-weight: 700;
    line-height: 1.15;
    color: #111827;
    margin-bottom: 6px;
}
.nk-stat-label {
    font-size: 13px;
    color: #4b5563;
}
.nk-icon-box {
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    border-radius: 12px;
    background: #eef2f6;
    display: flex;
    align-items: center;
    justify-content: center;
}
.nk-icon-box svg {
    width: 21px;
    height: 21px;
    stroke: #111827;
    stroke-width: 1.8;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* 2. Dua Kartu Grafik Vertikal */
.nk-charts-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 20px;
}
.nk-panel {
    padding: 22px 24px;
    margin-bottom: 20px;
}
.nk-title {
    margin: 0 0 18px;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}
.nk-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.nk-head .nk-title {
    margin: 0;
}

/* Tombol Cetak & Unduh Laporan (gaya hasil.blade) */
.nk-aksi-kanan {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.btn-oranye {
    height: 44px;
    padding: 0 20px;
    border: 0;
    border-radius: 11px;
    background: #ff941c;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background 0.2s;
}
.btn-oranye:hover {
    background: #e8850f;
}
.btn-oranye svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    stroke-width: 1.8;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.nk-vchart {
    position: relative;
    height: 225px;
    padding: 24px 8px 30px 36px;
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    gap: 16px;
    border-bottom: 1px solid #d0d5dd;
    background-image: repeating-linear-gradient(
        to top,
        #e5e7eb 0px,
        #e5e7eb 1px,
        transparent 1px,
        transparent 50px
    );
    background-position: 36px 24px;
    background-size: calc(100% - 44px) 200px;
    background-repeat: no-repeat;
}
.nk-vbar-col {
    flex: 1 1 0;
    max-width: 100px;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    position: relative;
}
.nk-vbar-val {
    font-size: 12.5px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 6px;
    white-space: nowrap;
}
.nk-vbar-track {
    width: 58px;
    max-width: 100%;
    flex: 1 1 auto;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}
.nk-vbar-fill {
    width: 100%;
    min-height: 4px;
    border-radius: 10px 10px 0 0;
    background: #ffcfac;
    transition: height 0.25s ease;
}
.nk-vbar-fill.utama {
    background: #ff941c;
}
.nk-vbar-label {
    position: absolute;
    bottom: -26px;
    font-size: 12.5px;
    color: #4b5563;
    white-space: nowrap;
}

/* 3. Filter & Tabel Nilai KPI */
.nk-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.nk-search-wrap {
    position: relative;
    flex: 1 1 250px;
    max-width: 320px;
    min-width: 210px;
}
.nk-search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    stroke: #6b7280;
    stroke-width: 2;
    fill: none;
    pointer-events: none;
}
.nk-input {
    width: 100%;
    height: 40px;
    padding: 0 14px 0 38px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    background: #fff;
    color: #111827;
    font-size: 13.5px;
    outline: none;
}
.nk-input::placeholder {
    color: #9ca3af;
}
.nk-select {
    height: 40px;
    min-width: 155px;
    padding: 0 36px 0 14px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    background-color: #fff;
    color: #111827;
    font-size: 13.5px;
    font-weight: 500;
    outline: none;
    cursor: pointer;
    -webkit-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23111827' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}
.nk-input:focus,
.nk-select:focus {
    border-color: #ff941c;
}

.nk-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
}
.nk-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 980px;
}
.nk-table thead {
    background: #eef2f6;
}
.nk-table th {
    height: 44px;
    padding: 0 14px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
}
.nk-table td {
    height: 54px;
    padding: 0 14px;
    border-top: 1px solid #e5e7eb;
    font-size: 13.5px;
    color: #111827;
    vertical-align: middle;
    white-space: nowrap;
}
.nk-table tbody tr:hover {
    background: #fafafa;
}
.nk-nama {
    font-weight: 600;
    color: #111827;
}

/* Pill Badge */
.nk-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}
.nk-badge-hijau {
    background: #e6f4e9;
    color: #16834f;
}
.nk-badge-biru {
    background: #dbeafe;
    color: #1d4ed8;
}
.nk-badge-kuning {
    background: rgba(255, 207, 172, 0.55);
    color: #9a4a00;
}
.nk-badge-merah {
    background: rgba(234, 74, 76, 0.14);
    color: #c62828;
}
.nk-badge-netral {
    background: #eef2f6;
    color: #475467;
}

/* Footer & Paginasi */
.nk-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    margin-top: 16px;
}
.nk-info {
    font-size: 13px;
    color: #6b7280;
}
.nk-pager {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.nk-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    background: #fff;
    color: #374151;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}
.nk-page-btn:hover:not(.disabled):not(.active) {
    border-color: #ff941c;
    color: #ff941c;
}
.nk-page-btn.active {
    background: #ff941c;
    border-color: #ff941c;
    color: #fff;
}
.nk-page-btn.disabled {
    opacity: 0.45;
    pointer-events: none;
}
.nk-empty {
    text-align: center !important;
    padding: 32px 16px !important;
    color: #6b7280 !important;
    font-size: 14px;
}

@media (max-width: 1100px) {
    .nk-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 820px) {
    .nk-charts-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 640px) {
    .nk-stats-grid {
        grid-template-columns: 1fr;
    }
    .nk-search-wrap {
        max-width: none;
    }
}

@media print {
    .nk-toolbar,
    .btn-oranye,
    .nk-pager {
        display: none !important;
    }
}
</style>

<div class="nk-page">
    @if (! $pilihan)
        <div class="nk-card nk-panel">
            <p class="nk-info" style="margin: 0;">Belum ada periode penilaian.</p>
        </div>
    @else
        <!-- 1. 4 Kartu Ringkasan Atas -->
        <section class="nk-stats-grid">
            <div class="nk-card nk-stat-card">
                <div>
                    <div class="nk-stat-number">{{ \App\Support\Format::nilai($rataRekap) }}</div>
                    <div class="nk-stat-label">Rata-rata Unit {{ $tahunAktif }}</div>
                </div>
                <div class="nk-icon-box">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 3v18h18"/>
                        <path d="M7 16v-5"/>
                        <path d="M12 16V8"/>
                        <path d="M17 16v-3"/>
                    </svg>
                </div>
            </div>

            <div class="nk-card nk-stat-card">
                <div>
                    <div class="nk-stat-number">{{ $jumlahRekap }}</div>
                    <div class="nk-stat-label">Karyawan Dinilai</div>
                </div>
                <div class="nk-icon-box">
                    <svg viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
            </div>

            <div class="nk-card nk-stat-card">
                <div>
                    <div class="nk-stat-number">{{ \App\Support\Format::nilai($tertinggiRekap) }}</div>
                    <div class="nk-stat-label">Nilai Tertinggi</div>
                </div>
                <div class="nk-icon-box">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="5"/>
                        <path d="m8.5 13-1.5 8 5-3 5 3-1.5-8"/>
                    </svg>
                </div>
            </div>

            <div class="nk-card nk-stat-card">
                <div>
                    <div class="nk-stat-number">{{ \App\Support\Format::nilai($terendahRekap) }}</div>
                    <div class="nk-stat-label">Nilai Terendah</div>
                </div>
                <div class="nk-icon-box">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>
                </div>
            </div>
        </section>

        <!-- 2. 2 Grafik Batang (Distribusi Kategori & Rata-rata Unit Tahun) -->
        <section class="nk-charts-grid">
            <div class="nk-card nk-panel" style="margin-bottom: 0;">
                <h2 class="nk-title">Distribusi Kategori &middot; {{ $tahunAktif }}</h2>
                <div class="nk-vchart">
                    @foreach ($kategoriUrut as $namaKat)
                        @php
                            $jmlKat = (int) ($sebaran[$namaKat] ?? 0);
                            $tinggiPersen = $jmlKat > 0 ? max(8, min(100, ($jmlKat / $maksDistribusi) * 100)) : 2;
                        @endphp
                        <div class="nk-vbar-col">
                            <div class="nk-vbar-val">{{ $jmlKat }}</div>
                            <div class="nk-vbar-track">
                                <div class="nk-vbar-fill {{ $loop->first ? 'utama' : '' }}" style="height: {{ $tinggiPersen }}%;"></div>
                            </div>
                            <div class="nk-vbar-label">{{ $namaKat }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="nk-card nk-panel" style="margin-bottom: 0;">
                <h2 class="nk-title">Rata-rata Unit &middot; {{ $tahunAktif }}</h2>
                <div class="nk-vchart">
                    @foreach ($batangTriwulan as [$labelTw, $nilaiTw])
                        @php
                            $angkaTw = $nilaiTw === null ? 0 : max(0, min(100, (float) $nilaiTw));
                            $tinggiTw = $angkaTw > 0 ? max(8, $angkaTw) : 2;
                        @endphp
                        <div class="nk-vbar-col">
                            <div class="nk-vbar-val">{{ \App\Support\Format::nilai($nilaiTw) }}</div>
                            <div class="nk-vbar-track">
                                <div class="nk-vbar-fill {{ $loop->last ? 'utama' : '' }}" style="height: {{ $tinggiTw }}%;"></div>
                            </div>
                            <div class="nk-vbar-label">{{ $labelTw }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- 3. Tabel Nilai dan Kategori KPI -->
        <section class="nk-card nk-panel" style="margin-bottom: 0;">
            <div class="nk-head">
                <h2 class="nk-title">Nilai dan kategori KPI &middot; {{ $tahunAktif }}</h2>

                <div class="nk-aksi-kanan">
                    <button type="button" class="btn-oranye" onclick="window.print()">
                        <svg viewBox="0 0 24 24">
                            <polyline points="6 9 6 2 18 2 18 9"/>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                            <rect x="6" y="14" width="12" height="8"/>
                        </svg>
                        Cetak
                    </button>
                    <a href="{{ $urlUnduhLaporan }}" class="btn-oranye">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Unduh Laporan
                    </a>
                </div>
            </div>

            <form method="GET" class="nk-toolbar" id="filterNilaiKpiForm">
                <div class="nk-search-wrap">
                    <svg class="nk-search-icon" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari nama atau NIP..."
                        class="nk-input"
                        aria-label="Cari nama atau NIP"
                    >
                </div>

                <select name="tahun" onchange="this.form.submit()" class="nk-select" aria-label="Pilih Tahun">
                    @foreach ($tahunOpsi as $thn)
                        <option value="{{ $thn }}" @selected($tahunAktif === (string) $thn)>{{ $thn }}</option>
                    @endforeach
                </select>

                <select name="kategori" onchange="this.form.submit()" class="nk-select" aria-label="Filter Kategori">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriUrut as $k)
                        <option value="{{ $k }}" @selected(request('kategori') === $k)>{{ $k }}</option>
                    @endforeach
                </select>
            </form>

            <div class="nk-table-wrap">
                <table class="nk-table">
                    <thead>
                        <tr>
                            <th style="width: 56px;">No</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Triwulan 1</th>
                            <th>Triwulan 2</th>
                            <th>Triwulan 3</th>
                            <th>Triwulan 4</th>
                            <th>Semester 1</th>
                            <th>Semester 2</th>
                            <th>Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($koleksiBaris as $r)
                            @php
                                $noUrut = $isPaginator
                                    ? (($rawBaris->firstItem() ?? 1) + $loop->index)
                                    : $loop->iteration;

                                $nTw1 = $r['tw1'] ?? ($r['triwulan_1'] ?? null);
                                $nTw2 = $r['tw2'] ?? ($r['triwulan_2'] ?? null);
                                $nTw3 = $r['tw3'] ?? ($r['triwulan_3'] ?? null);
                                $nTw4 = $r['tw4'] ?? ($r['triwulan_4'] ?? null);

                                $nSem1 = $r['semester_1'] ?? (($nTw1 !== null || $nTw2 !== null)
                                    ? (((float) ($nTw1 ?? 0) + (float) ($nTw2 ?? 0)) / (($nTw1 !== null ? 1 : 0) + ($nTw2 !== null ? 1 : 0) ?: 1))
                                    : null);

                                $nSem2 = $r['semester_2'] ?? (($nTw3 !== null || $nTw4 !== null)
                                    ? (((float) ($nTw3 ?? 0) + (float) ($nTw4 ?? 0)) / (($nTw3 !== null ? 1 : 0) + ($nTw4 !== null ? 1 : 0) ?: 1))
                                    : null);

                                $predikat = $r['predikat'] ?? null;
                                $lowerKat = strtolower((string) $predikat);
                                $kelasKat = str_contains($lowerKat, 'sangat')
                                    ? 'nk-badge-hijau'
                                    : (str_contains($lowerKat, 'baik')
                                        ? 'nk-badge-biru'
                                        : (str_contains($lowerKat, 'cukup')
                                            ? 'nk-badge-kuning'
                                            : (str_contains($lowerKat, 'kurang') ? 'nk-badge-merah' : 'nk-badge-netral')));
                            @endphp
                            <tr>
                                <td>{{ $noUrut }}</td>
                                <td class="nk-nama">{{ $r['karyawan']->nama_karyawan ?? '—' }}</td>
                                <td>{{ $r['karyawan']->jabatan ?? '—' }}</td>
                                <td>{{ \App\Support\Format::nilai($nTw1) }}</td>
                                <td>{{ \App\Support\Format::nilai($nTw2) }}</td>
                                <td>{{ \App\Support\Format::nilai($nTw3) }}</td>
                                <td>{{ \App\Support\Format::nilai($nTw4) }}</td>
                                <td><strong>{{ \App\Support\Format::nilai($nSem1) }}</strong></td>
                                <td><strong>{{ \App\Support\Format::nilai($nSem2) }}</strong></td>
                                <td>
                                    @if ($predikat)
                                        <span class="nk-badge {{ $kelasKat }}">{{ $predikat }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="nk-empty">Belum ada nilai tervalidasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="nk-footer">
                <div class="nk-info">
                    Menampilkan {{ $dariData }}-{{ $sampaiData }} dari {{ $totalDitampilkan }} data
                </div>

                @if ($isPaginator && $rawBaris->lastPage() > 1)
                    @php
                        $current = $rawBaris->currentPage();
                        $last = $rawBaris->lastPage();
                    @endphp
                    <div class="nk-pager">
                        <a href="{{ $rawBaris->previousPageUrl() ?: '#' }}" class="nk-page-btn {{ $rawBaris->onFirstPage() ? 'disabled' : '' }}" aria-label="Halaman Sebelumnya">&lt;</a>
                        @for ($p = 1; $p <= $last; $p++)
                            <a href="{{ $rawBaris->url($p) }}" class="nk-page-btn {{ $p === $current ? 'active' : '' }}">{{ $p }}</a>
                        @endfor
                        <a href="{{ $rawBaris->nextPageUrl() ?: '#' }}" class="nk-page-btn {{ $rawBaris->hasMorePages() ? '' : 'disabled' }}" aria-label="Halaman Berikutnya">&gt;</a>
                    </div>
                @endif
            </div>
        </section>
    @endif
</div>
@endsection