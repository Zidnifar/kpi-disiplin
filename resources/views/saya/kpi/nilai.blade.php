@extends('layouts.app')

@section('judul', 'Nilai KPI')

@section('isi')
@php
    $fmt = fn ($v) => \App\Support\Format::nilai($v);

    $tahunPilihanList = collect($tahunPilihan ?? []);
    $tahunAktif = (int) ($tahun ?? now()->year);
    $triwulanPilihanList = collect($triwulanPilihan ?? [1, 2, 3, 4]);
    $triwulanAktif = (int) ($triwulan ?? 1);

    $terakhirData = $terakhir ?? null;
    $semesterMap = collect($semester ?? []);
    $twMap = $tw ?? [];
    $skalaList = collect($skala ?? []);

    $dataRincian = $hasilRincian ?? ($hasil ?? ($twMap[$triwulanAktif] ?? null));
    if ($dataRincian && ! $dataRincian->relationLoaded('detail')) {
        $dataRincian->load(['detail.indikator', 'predikat']);
    }

    $detailList = collect($dataRincian?->detail ?? []);

    $kategoriTerakhir = strtolower((string) ($terakhirData?->predikat?->nama_predikat ?? ''));
    $badgeTerakhir = str_contains($kategoriTerakhir, 'cukup') ? 'kpi-oranye'
        : ((str_contains($kategoriTerakhir, 'kurang') || str_contains($kategoriTerakhir, 'buruk')) ? 'kpi-merah' : 'kpi-hijau');

    $nilaiTerakhirAngka = $terakhirData?->nilai_triwulan;
@endphp

<style>
.kpi-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 8px 28px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.kpi-page * {
    box-sizing: border-box;
}
.kpi-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
    padding: 22px 24px;
    margin-bottom: 20px;
}

/* 3 Kartu Ringkasan Nilai */
.kpi-tiga-kartu {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 20px;
}
.kpi-ringkasan-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
    padding: 20px 22px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 124px;
}
.kpi-ringkasan-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
}
.kpi-ringkasan-top h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 500;
    color: #344054;
}
.kpi-nilai-besar {
    font-size: 34px;
    font-weight: 700;
    line-height: 1.1;
    color: #111827;
}
.kpi-muted {
    margin: 0;
    font-size: 13px;
    color: #6b7280;
}
.kpi-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}
.kpi-hijau {
    background: #e6f4e9;
    color: #16834f;
}
.kpi-biru {
    background: #dbeafe;
    color: #1d4ed8;
}
.kpi-oranye {
    background: rgba(255, 207, 172, 0.55);
    color: #9a4a00;
}
.kpi-merah {
    background: rgba(234, 74, 76, 0.12);
    color: #c62828;
}
.kpi-netral {
    background: #eef2f6;
    color: #475467;
}

/* 2 Kolom Grafik & Skala Predikat */
.kpi-dua-kartu {
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(290px, 1fr);
    gap: 20px;
    margin-bottom: 20px;
    align-items: stretch;
}
.kpi-card-title {
    margin: 0 0 18px;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}
.kpi-plot {
    position: relative;
    height: 195px;
    margin: 28px 0 0 38px;
    border-bottom: 1.5px solid #cbd5e1;
}
.kpi-grid-line {
    position: absolute;
    left: 0;
    right: 0;
    border-top: 1px solid #eef0f4;
}
.kpi-y-label {
    position: absolute;
    left: -38px;
    width: 30px;
    text-align: right;
    font-size: 12px;
    color: #6b7280;
    transform: translateY(-50%);
}
.kpi-bars {
    position: absolute;
    inset: 0;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
}
.kpi-bar-col {
    position: relative;
    height: 100%;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}
.kpi-bar-val {
    position: absolute;
    font-size: 12px;
    font-weight: 600;
    color: #111827;
}
.kpi-bar {
    width: min(46px, 58%);
    background: #fdd2a0;
    border-radius: 8px 8px 4px 4px;
}
.kpi-bar.terakhir {
    background: #ff941c;
}
.kpi-bar-empty {
    width: 44px;
    height: 36px;
    border: 1.5px dashed #cbd5e1;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 13px;
    margin-bottom: 4px;
}
.kpi-x-labels {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin: 8px 0 0 38px;
    text-align: center;
    font-size: 12px;
    color: #6b7280;
}

/* Skala Predikat */
.kpi-skala-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.kpi-skala-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 9px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    font-size: 13px;
}
.kpi-skala-item.aktif {
    border-color: #ff941c;
    background: #fffaf4;
}

/* Filter Triwulan & Tahun */
.kpi-filter-card {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 16px;
    flex-wrap: wrap;
}
.kpi-filter-form {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.kpi-select {
    height: 40px;
    min-width: 165px;
    padding: 0 38px 0 14px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    background-color: #fff;
    color: #111827;
    font-size: 14px;
    font-weight: 500;
    outline: none;
    cursor: pointer;
    -webkit-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23111827' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}

/* Tabel Rincian */
.kpi-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
}
.kpi-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 680px;
}
.kpi-table thead {
    background: #eef2f6;
}
.kpi-table th {
    height: 44px;
    padding: 0 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
}
.kpi-table td {
    height: 50px;
    padding: 0 16px;
    border-top: 1px solid #e5e7eb;
    font-size: 13.5px;
    color: #111827;
    vertical-align: middle;
}
.kpi-table th.kanan,
.kpi-table td.kanan {
    text-align: right;
}
.kpi-table tfoot td {
    height: 48px;
    padding: 0 16px;
    border-top: 1.5px solid #cbd5e1;
    background: #f8fafc;
    font-weight: 700;
    font-size: 14px;
    color: #111827;
}
.kpi-subtext {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
}

@media (max-width: 1050px) {
    .kpi-tiga-kartu {
        grid-template-columns: 1fr;
    }
    .kpi-dua-kartu {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="kpi-page">
    <!-- 1. 3 Kartu Ringkasan Nilai -->
    <div class="kpi-tiga-kartu">
        <div class="kpi-ringkasan-card">
            <div class="kpi-ringkasan-top">
                <h3>{{ $terakhirData?->periode?->nama_periode ?? 'Triwulan terakhir' }}</h3>
                @if ($terakhirData?->predikat)
                    <span class="kpi-badge {{ $badgeTerakhir }}">{{ $terakhirData->predikat->nama_predikat }}</span>
                @endif
            </div>
            <div class="kpi-nilai-besar">{{ $fmt($terakhirData?->nilai_triwulan) }}</div>
        </div>

        @foreach ([1 => [1, 2], 2 => [3, 4]] as $smt => [$a, $b])
            @php
                $s = $semesterMap->get($smt);
                $namaPredSmt = strtolower((string) ($s?->predikat?->nama_predikat ?? ''));
                $badgeSmt = str_contains($namaPredSmt, 'cukup') ? 'kpi-oranye'
                    : ((str_contains($namaPredSmt, 'kurang') || str_contains($namaPredSmt, 'buruk')) ? 'kpi-merah' : 'kpi-biru');
            @endphp
            <div class="kpi-ringkasan-card">
                <div class="kpi-ringkasan-top">
                    <h3>Semester {{ $smt }} {{ $tahunAktif }}</h3>
                    @if ($s && $s->predikat)
                        <span class="kpi-badge {{ $badgeSmt }}">{{ $s->predikat->nama_predikat }}</span>
                    @elseif (! $s)
                        <span class="kpi-badge kpi-netral">Berjalan</span>
                    @endif
                </div>
                <div class="kpi-nilai-besar">{{ $s ? $fmt($s->nilai_semester) : '—' }}</div>
            </div>
        @endforeach
    </div>

    <!-- 2. Grafik Nilai per Triwulan & Skala Predikat -->
    <div class="kpi-dua-kartu">
        <div class="kpi-card" style="margin-bottom: 0;">
            <h2 class="kpi-card-title">Nilai per Triwulan {{ $tahunAktif }}</h2>
            <div class="kpi-plot">
                @foreach ([0, 25, 50, 75, 100] as $y)
                    @if ($y > 0)
                        <div class="kpi-grid-line" style="bottom: {{ $y }}%;"></div>
                    @endif
                    <span class="kpi-y-label" style="bottom: {{ $y }}%;">{{ $y }}</span>
                @endforeach

                <div class="kpi-bars">
                    @foreach ([1, 2, 3, 4] as $i)
                        @php
                            $itemTw = $twMap[$i] ?? null;
                            $nilaiTw = $itemTw?->nilai_triwulan;
                            $tinggi = $nilaiTw !== null ? max(0, min(100, (float) $nilaiTw)) : 0;
                            $isTerakhir = ($terakhirData?->id_periode !== null) && ($terakhirData?->id_periode === $itemTw?->id_periode);
                        @endphp
                        <div class="kpi-bar-col">
                            @if ($nilaiTw !== null)
                                <span class="kpi-bar-val" style="bottom: calc({{ $tinggi }}% + 6px);">{{ $fmt($nilaiTw) }}</span>
                                <div class="kpi-bar {{ $isTerakhir ? 'terakhir' : '' }}" style="height: {{ $tinggi }}%;"></div>
                            @else
                                <div class="kpi-bar-empty">…</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="kpi-x-labels">
                @foreach ([1, 2, 3, 4] as $i)
                    <span>Triwulan {{ $i }}</span>
                @endforeach
            </div>
        </div>

        <div class="kpi-card" style="margin-bottom: 0;">
            <h2 class="kpi-card-title">Skala Predikat</h2>
            <ul class="kpi-skala-list">
                @foreach ($skalaList as $p)
                    @php
                        $namaP = strtolower((string) $p->nama_predikat);
                        $badgeP = str_contains($namaP, 'cukup') ? 'kpi-oranye'
                            : ((str_contains($namaP, 'kurang') || str_contains($namaP, 'buruk')) ? 'kpi-merah'
                            : (str_contains($namaP, 'baik') && ! str_contains($namaP, 'sangat') ? 'kpi-biru' : 'kpi-hijau'));
                        $cocok = $nilaiTerakhirAngka !== null
                            && (float) $nilaiTerakhirAngka >= (float) $p->nilai_min
                            && (float) $nilaiTerakhirAngka <= (float) $p->nilai_max;
                    @endphp
                    <li class="kpi-skala-item {{ $cocok ? 'aktif' : '' }}">
                        <span class="kpi-badge {{ $badgeP }}">{{ $p->nama_predikat }}</span>
                        <strong>{{ $fmt($p->nilai_min) }} – {{ $fmt($p->nilai_max) }}</strong>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- 3. Filter Triwulan & Tahun -->
    <div class="kpi-card kpi-filter-card">
        <form method="GET" class="kpi-filter-form">
            <select name="triwulan" onchange="this.form.submit()" class="kpi-select" aria-label="Pilih Triwulan">
                @foreach ($triwulanPilihanList as $twOpt)
                    <option value="{{ $twOpt }}" @selected((int) $twOpt === $triwulanAktif)>Triwulan {{ $twOpt }}</option>
                @endforeach
            </select>

            @if ($tahunPilihanList->isNotEmpty())
                <select name="tahun" onchange="this.form.submit()" class="kpi-select" aria-label="Pilih Tahun">
                    @foreach ($tahunPilihanList as $t)
                        <option value="{{ $t }}" @selected((int) $t === $tahunAktif)>Tahun {{ $t }}</option>
                    @endforeach
                </select>
            @endif
        </form>
    </div>

    <!-- 4. Tabel Rincian di Bawah Filter -->
    @php
        $judulRincian = $dataRincian?->periode?->nama_periode
            ?? ('Triwulan ' . $triwulanAktif . ' ' . $tahunAktif);
    @endphp

    <div class="kpi-card" style="margin-bottom: 0;">
        <h2 class="kpi-card-title">Rincian Poin Pengurang · {{ $judulRincian }}</h2>

        @if (! $dataRincian)
            <p class="kpi-muted">Belum ada hasil KPI yang divalidasi untuk Triwulan {{ $triwulanAktif }} Tahun {{ $tahunAktif }}.</p>
        @elseif ($detailList->isNotEmpty())
            <div class="kpi-table-wrap">
                <table class="kpi-table">
                    <thead>
                        <tr>
                            <th style="width: 56px;">No</th>
                            <th>Kode</th>
                            <th>Kriteria</th>
                            <th class="kanan">Frekuensi</th>
                            <th class="kanan">Bobot (poin)</th>
                            <th class="kanan">Total Pengurang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalRincian = 0; @endphp
                        @foreach ($detailList as $det)
                            @php
                                $kode = $det->indikator?->kode_indikator
                                    ?? $det->indikator?->kode
                                    ?? $det->kode
                                    ?? '—';
                                $kriteria = $det->indikator?->nama_indikator
                                    ?? $det->indikator?->nama
                                    ?? $det->kriteria
                                    ?? '—';
                                $keterangan = $det->keterangan ?? $det->catatan ?? null;
                                $frekuensi = (float) ($det->frekuensi ?? $det->jumlah ?? 0);
                                $bobot = (float) ($det->bobot ?? $det->indikator?->bobot ?? ($frekuensi > 0 ? (($det->total_pengurang ?? $det->poin ?? 0) / $frekuensi) : 0));
                                $pengurang = (float) ($det->total_pengurang ?? $det->poin ?? ($frekuensi * $bobot));
                                $totalRincian += $pengurang;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $kode }}</strong></td>
                                <td>
                                    {{ $kriteria }}
                                    @if ($keterangan)
                                        <span class="kpi-subtext">{{ $keterangan }}</span>
                                    @endif
                                </td>
                                <td class="kanan">{{ $fmt($frekuensi) }}</td>
                                <td class="kanan">{{ $fmt($bobot) }}</td>
                                <td class="kanan"><strong>− {{ $fmt($pengurang) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">Total</td>
                            <td class="kanan">− {{ $fmt($dataRincian->total_pengurang ?? $totalRincian) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            @include('partials.rincian-kpi', ['hasil' => $dataRincian])
        @endif
    </div>
</div>
@endsection