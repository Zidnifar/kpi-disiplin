@extends('layouts.app')

@section('title', 'Pemeriksaan KPI')
@section('judul', 'Pemeriksaan KPI')

@section('isi')
@php
    $namaPredikat = $hasil->predikat->nama_predikat ?? null;
    $lowerKat = strtolower((string) $namaPredikat);
    $kelasKat = str_contains($lowerKat, 'sangat')
        ? 'pkd-badge-hijau'
        : (str_contains($lowerKat, 'baik')
            ? 'pkd-badge-biru'
            : (str_contains($lowerKat, 'kurang') || str_contains($lowerKat, 'buruk') ? 'pkd-badge-merah' : 'pkd-badge-netral'));

    $sudahDiperiksa = strtolower((string) ($hasil->status_periksa ?? '')) === 'sudah diperiksa';
    $statusHitung = $hasil->status_hitung ?? 'Sudah dihitung';
@endphp

<style>
.pkd-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 8px 32px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.pkd-page * {
    box-sizing: border-box;
}
.pkd-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
}

/* Breadcrumb */
.pkd-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 16px;
}
.pkd-breadcrumb a {
    color: #6b7280;
    text-decoration: none;
}
.pkd-breadcrumb a:hover {
    color: #111827;
}
.pkd-breadcrumb strong {
    color: #111827;
    font-weight: 600;
}

/* Baris Atas: Data Karyawan & Nilai Triwulan */
.pkd-top-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.85fr) minmax(260px, 1fr);
    gap: 18px;
    margin-bottom: 20px;
    align-items: stretch;
}
.pkd-panel {
    padding: 22px 24px;
}
.pkd-title {
    margin: 0 0 18px;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

.pkd-info-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px 24px;
}
.pkd-info-label {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
}
.pkd-info-val {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

/* Kartu Nilai Triwulan Kanan */
.pkd-score-card {
    padding: 22px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 10px;
}
.pkd-score-label {
    font-size: 13px;
    color: #6b7280;
}
.pkd-score-number {
    font-size: 36px;
    font-weight: 700;
    line-height: 1.1;
    color: #111827;
}

/* Kartu Rincian Poin Pengurang */
.pkd-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.pkd-card-head .pkd-title {
    margin: 0;
}

/* Styling Tabel di dalam partials.rincian-kpi */
.pkd-rincian-wrap table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
}
.pkd-rincian-wrap thead {
    background: #eef2f6;
}
.pkd-rincian-wrap th {
    height: 44px;
    padding: 0 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    border-bottom: 1px solid #d0d5dd;
}
.pkd-rincian-wrap td {
    height: 50px;
    padding: 0 16px;
    font-size: 13.5px;
    color: #111827;
    border-top: 1px solid #e5e7eb;
    vertical-align: middle;
}
.pkd-rincian-wrap tr:last-child td {
    font-weight: 700;
}

/* Badge */
.pkd-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}
.pkd-badge-hijau {
    background: #e6f4e9;
    color: #16834f;
}
.pkd-badge-biru {
    background: #dbeafe;
    color: #1d4ed8;
}
.pkd-badge-merah {
    background: rgba(234, 74, 76, 0.14);
    color: #c62828;
}
.pkd-badge-oranye {
    background: rgba(255, 207, 172, 0.55);
    color: #9a4a00;
}
.pkd-badge-netral {
    background: #eef2f6;
    color: #475467;
}

/* Catatan & Tombol Aksi Bawah */
.pkd-catatan-box {
    margin-top: 18px;
}
.pkd-catatan-box label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 6px;
}
.pkd-catatan-box textarea {
    width: 100%;
    min-height: 76px;
    padding: 10px 14px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    font-family: inherit;
    font-size: 13.5px;
    color: #111827;
    resize: vertical;
    outline: none;
}
.pkd-catatan-box textarea:focus {
    border-color: #ff941c;
}
.pkd-galat {
    margin-top: 6px;
    font-size: 12.5px;
    color: #c62828;
}
.pkd-notice {
    margin-top: 16px;
    padding: 12px 16px;
    border-radius: 10px;
    background: #eef2f6;
    color: #374151;
    font-size: 13px;
}

.pkd-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 22px;
    flex-wrap: wrap;
}
.btn-pkd-kembali {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 40px;
    padding: 0 18px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    background: #fff;
    color: #111827;
    font-size: 13.5px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-pkd-kembali:hover {
    border-color: #9ca3af;
    background: #f9fafb;
}
.btn-pkd-simpan {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 40px;
    padding: 0 20px;
    border: 0;
    border-radius: 10px;
    background: #ff941c;
    color: #fff;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
}
.btn-pkd-simpan:hover {
    background: #e8830f;
}
.btn-pkd-kembali svg,
.btn-pkd-simpan svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    stroke-width: 2;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

@media (max-width: 960px) {
    .pkd-top-grid {
        grid-template-columns: minmax(0, 1fr);
    }
}
@media (max-width: 640px) {
    .pkd-info-grid {
        grid-template-columns: 1fr;
    }
    .pkd-actions {
        justify-content: stretch;
    }
    .btn-pkd-kembali,
    .btn-pkd-simpan {
        flex: 1 1 auto;
        justify-content: center;
    }
}
</style>

<div class="pkd-page">
    <nav class="pkd-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('unit.kpi.pemeriksaan', ['periode' => $hasil->id_periode]) }}">Pemeriksaan KPI</a>
        <span>/</span>
        <strong>Detail &middot; {{ $hasil->karyawan->nama_karyawan ?? '—' }}</strong>
    </nav>

    <!-- Baris Atas: Data Karyawan & Nilai Triwulan -->
    <section class="pkd-top-grid">
        <div class="pkd-card pkd-panel">
            <h2 class="pkd-title">Data Karyawan</h2>

            <div class="pkd-info-grid">
                <div>
                    <div class="pkd-info-label">NIP</div>
                    <div class="pkd-info-val">{{ $hasil->karyawan->nip ?? '—' }}</div>
                </div>
                <div>
                    <div class="pkd-info-label">Nama</div>
                    <div class="pkd-info-val">{{ $hasil->karyawan->nama_karyawan ?? '—' }}</div>
                </div>
                <div>
                    <div class="pkd-info-label">Jabatan</div>
                    <div class="pkd-info-val">{{ $hasil->karyawan->jabatan ?? '—' }}</div>
                </div>
                <div>
                    <div class="pkd-info-label">Departemen</div>
                    <div class="pkd-info-val">{{ $hasil->karyawan->departemen->nama_dept ?? '—' }}</div>
                </div>
                <div>
                    <div class="pkd-info-label">Periode</div>
                    <div class="pkd-info-val">{{ $hasil->periode->nama_periode ?? '—' }}</div>
                </div>
                <div>
                    <div class="pkd-info-label">Perhitungan</div>
                    <div class="pkd-info-val">
                        <span class="pkd-badge pkd-badge-hijau">{{ $statusHitung }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pkd-card pkd-score-card">
            <div class="pkd-score-label">Nilai Triwulan</div>
            <div class="pkd-score-number">{{ \App\Support\Format::nilai($hasil->nilai_triwulan) }}</div>
            @if ($namaPredikat)
                <span class="pkd-badge {{ $kelasKat }}">{{ $namaPredikat }}</span>
            @endif
        </div>
    </section>

    <!-- Baris Bawah: Rincian Poin Pengurang & Aksi -->
    <section class="pkd-card pkd-panel">
        <div class="pkd-card-head">
            <h2 class="pkd-title">Rincian Poin Pengurang</h2>
            <span class="pkd-badge {{ $sudahDiperiksa ? 'pkd-badge-hijau' : 'pkd-badge-oranye' }}">
                {{ $sudahDiperiksa ? 'Sudah diperiksa' : 'Menunggu pemeriksaan' }}
            </span>
        </div>

        <div class="pkd-rincian-wrap">
            @include('partials.rincian-kpi', ['hasil' => $hasil])
        </div>

        @if (($hasil->status_validasi ?? null) === 'Tervalidasi')
            <div class="pkd-notice">
                Hasil ini sudah divalidasi admin, jadi tidak dapat diubah lagi.
            </div>
            <div class="pkd-actions">
                <a href="{{ route('unit.kpi.pemeriksaan', ['periode' => $hasil->id_periode]) }}" class="btn-pkd-kembali">
                    <svg viewBox="0 0 24 24">
                        <path d="M19 12H5"/>
                        <path d="m12 19-7-7 7-7"/>
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('unit.kpi.pemeriksaan.tandai', $hasil) }}">
                @csrf

                <div class="pkd-catatan-box">
                    <label for="catatan_periksa">Catatan Pemeriksaan (opsional)</label>
                    <textarea
                        id="catatan_periksa"
                        name="catatan_periksa"
                        placeholder="Tulis catatan hasil pemeriksaan..."
                    >{{ old('catatan_periksa', $hasil->catatan_periksa) }}</textarea>
                    @error('catatan_periksa')
                        <div class="pkd-galat">{{ $message }}</div>
                    @enderror
                </div>

                <div class="pkd-actions">
                    <a href="{{ route('unit.kpi.pemeriksaan', ['periode' => $hasil->id_periode]) }}" class="btn-pkd-kembali">
                        <svg viewBox="0 0 24 24">
                            <path d="M19 12H5"/>
                            <path d="m12 19-7-7 7-7"/>
                        </svg>
                        <span>Kembali</span>
                    </a>

                    <button type="submit" class="btn-pkd-simpan">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                        <span>{{ $sudahDiperiksa ? 'Perbarui Catatan' : 'Tandai Sudah Diperiksa' }}</span>
                    </button>
                </div>
            </form>
        @endif
    </section>
</div>
@endsection