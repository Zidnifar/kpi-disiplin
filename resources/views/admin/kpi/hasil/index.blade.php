@extends('layouts.app')
@section('title', 'Hasil KPI')
@section('judul', 'Hasil KPI')

@section('isi')
@if (! $periode)
    <div class="kpi-card">
        <p style="margin:0; color:#667085;">Belum ada periode penilaian. <a href="{{ route('admin.kpi.periode.create') }}">Buat periode</a> lebih dulu.</p>
    </div>
@else
@php
    // Data tambahan dari controller (diberi default supaya halaman tidak error)
    $distribusi = $distribusi ?? ['Sangat Baik' => 0, 'Baik' => 0, 'Cukup' => 0, 'Kurang' => 0];
    $indikator  = $indikator ?? [];   // contoh: [['label' => 'Kehadiran', 'nilai' => 94], ...]
    $totalDinilai = max((int) ($ringkasan['dinilai'] ?? 0), 1);
    $rata = $ringkasan['rata'] !== null ? \App\Support\Format::nilai(round((float) $ringkasan['rata'], 1)) : '—';
    $tertinggi = $ringkasan['tertinggi'] !== null ? \App\Support\Format::nilai((float) $ringkasan['tertinggi']) : '—';
@endphp

<div class="kpi-page">

    <!-- Baris atas: Ringkasan & Rata-rata per indikator -->
    <div class="kpi-top-grid">
        <div class="kpi-card">
            <h3 class="kpi-card-title">Ringkasan KPI {{ $periode->nama_periode }}</h3>
            <p class="kpi-small">Rata-rata nilai karyawan</p>
            <div class="kpi-big">{{ $rata }}</div>

            @foreach ($distribusi as $nama => $jumlah)
                <div class="kpi-bar-row">
                    <div class="kpi-bar-label">
                        <span>{{ $nama }}</span>
                        <span>{{ $jumlah }} karyawan</span>
                    </div>
                    <div class="kpi-bar-track">
                        <div class="kpi-bar-fill" style="width: {{ min(100, round($jumlah / $totalDinilai * 100)) }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="kpi-card">
            <h3 class="kpi-card-title">Rata-Rata per Indikator</h3>
            <div style="margin-top: 28px;">
                @forelse ($indikator as $i)
                    <div class="kpi-bar-row">
                        <div class="kpi-bar-label">
                            <span>{{ $i['label'] }}</span>
                            <span><strong>{{ \App\Support\Format::nilai($i['nilai']) }}</strong></span>
                        </div>
                        <div class="kpi-bar-track">
                            <div class="kpi-bar-fill" style="width: {{ min(100, max(0, (float) $i['nilai'])) }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="kpi-small">Belum ada data indikator untuk periode ini.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Periode Penilaian -->
    <div class="kpi-card" style="margin-bottom: 24px;">
        <h3 class="kpi-card-title">Periode Penilaian</h3>
        <form action="{{ route('admin.kpi.hasil.index') }}" method="GET" class="kpi-filter">
            <select name="triwulan" class="custom-select" onchange="this.form.submit()">
                @foreach ($triwulanList ?? [1, 2, 3, 4] as $t)
                    <option value="{{ $t }}" @selected((int) request('triwulan', $triwulanAktif ?? 0) === (int) $t)>Triwulan {{ $t }}</option>
                @endforeach
            </select>

            <select name="tahun" class="custom-select" onchange="this.form.submit()">
                @foreach ($tahunList ?? [now()->year] as $th)
                    <option value="{{ $th }}" @selected((int) request('tahun', $tahunAktif ?? now()->year) === (int) $th)>{{ $th }}</option>
                @endforeach
            </select>

            <div class="kpi-filter-aksi">
                <button type="button" class="btn-oranye" onclick="window.print()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Cetak
                </button>
                <a href="{{ route('admin.laporan') }}" class="btn-oranye">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Unduh Laporan
                </a>
            </div>
        </form>
    </div>

    <!-- Kartu ringkasan statistik -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-info"><h2>{{ $ringkasan['dinilai'] ?? 0 }}</h2><p>Karyawan Dinilai</p></div>
        </div>
        <div class="summary-card">
            <div class="summary-info"><h2>{{ $rata }}</h2><p>Rata-Rata Nilai</p></div>
        </div>
        <div class="summary-card">
            <div class="summary-info"><h2>{{ $ringkasan['valid'] ?? 0 }}</h2><p>Sudah Divalidasi</p></div>
        </div>
        <div class="summary-card">
            <div class="summary-info"><h2>{{ $tertinggi }}</h2><p>Nilai Tertinggi</p></div>
        </div>
    </div>

    <!-- Tabel hasil KPI -->
    <div class="content-card">
        <div class="content-heading">
            <h2>Hasil KPI setiap karyawan</h2>
            @if (($ringkasan['dinilai'] ?? 0) > ($ringkasan['valid'] ?? 0))
                <form method="POST" action="{{ route('admin.kpi.hasil.validasi-semua', $periode) }}" data-konfirmasi="Validasi semua hasil yang sudah diperiksa kepala unit?">
                    @csrf
                    <button type="submit" class="btn-oranye">Validasi Semua</button>
                </form>
            @endif
        </div>

        <div class="table-wrapper">
            <table class="kpi-table">
                <thead>
                    <tr>
                        <th style="width:70px;">No</th>
                        <th>Nama</th>
                        <th>Nilai</th>
                        <th>Kategori</th>
                        <th>Validasi</th>
                        <th>Rincian</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($daftar as $index => $h)
                    @php
                        $kategori = $h->predikat->nama_predikat ?? null;
                        $kategoriClass = match (strtolower((string) $kategori)) {
                            'sangat baik' => 'badge-hijau',
                            'baik'        => 'badge-biru',
                            'cukup'       => 'badge-kuning',
                            'kurang'      => 'badge-merah',
                            default       => 'badge-abu',
                        };
                        $validasiClass = match ($h->status_validasi) {
                            'Tervalidasi'        => 'badge-biru',
                            'Menunggu validasi'  => 'badge-oranye',
                            'Dikembalikan'       => 'badge-merah',
                            default              => 'badge-abu',
                        };
                        $validasiTeks = $h->status_validasi === 'Tervalidasi' ? 'Disetujui' : $h->status_validasi;
                    @endphp
                    <tr>
                        <td>{{ $daftar->firstItem() + $index }}</td>
                        <td>{{ $h->karyawan->nama_karyawan ?? '—' }}</td>
                        <td><strong>{{ \App\Support\Format::nilai($h->nilai_triwulan) }}</strong></td>
                        <td>
                            @if ($kategori)
                                <span class="status-badge {{ $kategoriClass }}">{{ $kategori }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="status-badge {{ $validasiClass }}">{{ $validasiTeks }}</span></td>
                        <td><button type="button" class="btn-rincian" onclick="bukaModalRincian('rincian-{{ $h->id_hasil }}')">Lihat rincian</button></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-data">
                            Belum ada hasil untuk periode ini. <a href="{{ route('admin.kpi.periode.hitung', $periode) }}">Hitung KPI</a> lebih dulu.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @foreach ($daftar as $h)
            @php
                $perlu = $perluDiperiksa[$h->id_hasil] ?? false;
                $sudahValid = $h->status_validasi === 'Tervalidasi';
                $belumDiperiksa = $perlu && $h->status_periksa !== 'Sudah diperiksa';
                $namaKategori = $h->predikat->nama_predikat ?? null;
                $kelasKategori = match (strtolower((string) $namaKategori)) {
                    'sangat baik' => 'badge-hijau',
                    'baik'        => 'badge-biru',
                    'cukup'       => 'badge-kuning',
                    'kurang'      => 'badge-merah',
                    default       => 'badge-abu',
                };
                $kelasStatus = match ($h->status_validasi) {
                    'Tervalidasi'       => 'badge-biru',
                    'Menunggu validasi' => 'badge-oranye',
                    'Dikembalikan'      => 'badge-merah',
                    default             => 'badge-abu',
                };
                $teksStatus = $sudahValid ? 'Disetujui' : $h->status_validasi;
            @endphp
            <template id="rincian-{{ $h->id_hasil }}">
                <div class="km-kartu">
                    <div class="km-kepala">
                        <div>
                            <h2>Rincian Hasil KPI</h2>
                            <p>{{ $h->periode->nama_periode ?? $periode->nama_periode }}</p>
                        </div>
                        <button type="button" class="km-tutup" onclick="tutupModalRincian()" aria-label="Tutup">×</button>
                    </div>

                    <!-- Identitas -->
                    <div class="km-identitas">
                        <div><span>Nama</span>{{ $h->karyawan->nama_karyawan }}</div>
                        <div><span>NIP</span>{{ $h->karyawan->nip ?? '—' }}</div>
                        <div><span>Departemen</span>{{ $h->karyawan->departemen->nama_dept ?? '—' }}</div>
                        <div><span>Jabatan</span>{{ $h->karyawan->jabatan ?? '—' }}</div>
                    </div>

                    <!-- Ringkasan nilai -->
                    <div class="km-ringkasan">
                        <div class="km-kotak">
                            <div class="km-angka">{{ \App\Support\Format::nilai($h->nilai_triwulan) }}</div>
                            <div class="km-label">Nilai triwulan</div>
                            <div class="km-label">{{ $h->nilai_awal }} − {{ $h->total_pengurang }} = {{ \App\Support\Format::nilai($h->nilai_triwulan) }}</div>
                        </div>
                        <div class="km-kotak">
                            <div style="margin-top: 4px;">
                                @if ($namaKategori)
                                    <span class="status-badge {{ $kelasKategori }}">{{ $namaKategori }}</span>
                                @else
                                    —
                                @endif
                            </div>
                            <div class="km-label" style="margin-top: 10px;">Kategori</div>
                        </div>
                        <div class="km-kotak">
                            <div style="margin-top: 4px;"><span class="status-badge {{ $kelasStatus }}">{{ $teksStatus }}</span></div>
                            <div class="km-label" style="margin-top: 10px;">Status validasi</div>
                        </div>
                    </div>

                    <!-- Rincian poin pengurang -->
                    <h3 class="km-judul">Rincian poin pengurang</h3>
                    <div class="km-tabel-bungkus">
                        <table class="km-tabel">
                            <thead>
                                <tr><th style="text-align: left;">Indikator</th><th>Frekuensi</th><th>Bobot poin</th><th>Total pengurang</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($h->detail as $d)
                                    <tr>
                                        <td style="text-align: left;">{{ $d->indikator->nama_indikator ?? ($d->indikator->kode_indikator ?? '—') }}</td>
                                        <td>{{ $d->frekuensi }}</td>
                                        <td>{{ $d->bobot_poin }}</td>
                                        <td>{{ $d->total_pengurang }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" style="color: #667085;">Tidak ada pelanggaran, jadi nilai awal tidak dikurangi.</td></tr>
                                @endforelse
                            </tbody>
                            @if ($h->detail->isNotEmpty())
                                <tfoot>
                                    <tr><td colspan="3" style="text-align: right;">Total pengurang</td><td>{{ $h->total_pengurang }}</td></tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    <!-- Pemeriksaan kepala unit -->
                    <h3 class="km-judul">Pemeriksaan kepala unit</h3>
                    <div class="km-identitas">
                        <div>
                            <span>Status pemeriksaan</span>
                            {{ $h->status_periksa }}@if ($h->pemeriksa) · {{ $h->pemeriksa->namaTampil() }}@endif
                            @if ($h->tanggal_periksa)<br><small>{{ \App\Support\Format::tanggal($h->tanggal_periksa) }}</small>@endif
                        </div>
                        <div>
                            <span>Wajib diperiksa kepala unit?</span>
                            {{ $perlu ? 'Ya' : 'Tidak' }}
                        </div>
                    </div>
                    @if ($h->catatan_periksa)
                        <div class="km-catatan">Catatan pemeriksaan: {{ $h->catatan_periksa }}</div>
                    @endif

                    @if (! $sudahValid)
                        @if ($belumDiperiksa)
                            <div class="km-peringatan">Hasil ini belum diperiksa kepala unit, jadi belum bisa divalidasi.</div>
                        @endif
                        <form method="POST" action="{{ route('admin.kpi.hasil.validasi', $h) }}">
                            @csrf
                            <div class="km-aksi">
                                <button type="button" class="km-btn km-btn-abu" onclick="tutupModalRincian()">Tutup</button>
                                <button type="submit" formaction="{{ route('admin.kpi.hasil.kembalikan', $h) }}" class="km-btn km-btn-merah" onclick="return confirm('Kembalikan hasil ini untuk dihitung ulang?')">Kembalikan untuk dihitung ulang</button>
                                <button type="submit" class="km-btn km-btn-oranye" @disabled($belumDiperiksa)>Validasi hasil</button>
                            </div>
                        </form>
                    @else
                        <div class="km-identitas">
                            <div><span>Divalidasi oleh</span>{{ $h->pemvalidasi?->namaTampil() ?? '—' }}</div>
                            <div><span>Tanggal validasi</span>{{ \App\Support\Format::tanggal($h->tanggal_validasi) }}</div>
                        </div>
                        <div class="km-aksi">
                            <button type="button" class="km-btn km-btn-oranye" onclick="tutupModalRincian()">Tutup</button>
                        </div>
                    @endif
                </div>
            </template>
        @endforeach

        <!-- Paginasi -->
        <div class="pagination-area">
            <div class="paginasi">
                <span class="paginasi-info">
                    @if ($daftar->total() > 0)
                        Menampilkan {{ $daftar->firstItem() }}–{{ $daftar->lastItem() }} dari {{ $daftar->total() }} data
                    @else
                        Tidak ada data
                    @endif
                </span>

                @if ($daftar->hasPages())
                    <nav class="paginasi-tombol" role="navigation" aria-label="Halaman">
                        @if ($daftar->onFirstPage())
                            <span class="nonaktif">‹</span>
                        @else
                            <a href="{{ $daftar->previousPageUrl() }}" rel="prev">‹</a>
                        @endif

                        @foreach ($daftar->linkCollection() as $link)
                            @if ($link['url'] === null)
                                @if ($link['label'] === '...')
                                    <span class="nonaktif">...</span>
                                @endif
                            @else
                                @if ($link['active'])
                                    <span class="aktif">{{ $link['label'] }}</span>
                                @else
                                    <a href="{{ $link['url'] }}">{!! $link['label'] == '&laquo; Previous' ? '‹' : ($link['label'] == 'Next &raquo;' ? '›' : $link['label']) !!}</a>
                                @endif
                            @endif
                        @endforeach

                        @if ($daftar->hasMorePages())
                            <a href="{{ $daftar->nextPageUrl() }}" rel="next">›</a>
                        @else
                            <span class="nonaktif">›</span>
                        @endif
                    </nav>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.kpi-page { width: 100%; padding: 0 8px 24px; color: #101828; }

.kpi-top-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.kpi-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 20px;
    padding: 22px 26px;
}

.kpi-card-title { 
    font-size: 16px; 
    font-weight: 700; 
    margin: 0 0 14px; 
    color: #101828; 
}
.kpi-small { 
    font-size: 12px; 
    color: #344054; 
    margin: 0 0 4px; 
}
.kpi-big { 
    font-size: 34px; 
    font-weight: 700; 
    line-height: 1.1; 
    margin-bottom: 16px; 
    color: #000; 
}

.kpi-bar-row { 
    margin-bottom: 14px; 
}
.kpi-bar-label { 
    display: flex; 
    justify-content: space-between; 
    font-size: 12px; 
    color: #344054; 
    margin-bottom: 5px; 
}
.kpi-bar-track { 
    height: 6px; 
    background: #d9dfeb; 
    border-radius: 999px; 
    overflow: hidden; 
}
.kpi-bar-fill { 
    height: 100%; 
    background: #ff941c; 
    border-radius: 999px; 
    min-width: 6px; 
}

.kpi-filter { 
    display: flex; 
    align-items: center; 
    gap: 14px; 
    flex-wrap: wrap; 
}
.kpi-filter .custom-select { 
    width: 220px; 
    flex: 0 0 auto; 
}
.kpi-filter-aksi { 
    margin-left: auto; 
    display: flex; 
    gap: 10px; 
    flex-wrap: wrap; 
}

.custom-select {
    height: 44px;
    width: 100%;
    border: 1px solid #d0dbea;
    border-radius: 11px;
    padding: 0 40px 0 14px;
    background: #fff;
    color: #101828;
    font-size: 14px;
    outline: none;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
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
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background 0.2s;
}
.btn-oranye:hover { background: #e8850f; }

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}
.summary-card {
    min-height: 96px;
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 20px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
}
.summary-info h2 { font-size: 34px; line-height: 1.1; font-weight: 700; color: #000; margin: 0 0 5px; }
.summary-info p { font-size: 13px; color: #344054; margin: 0; }

.content-card { background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; overflow: hidden; }
.content-heading {
    min-height: 70px;
    padding: 18px 23px 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}
.content-heading h2 { font-size: 20px; font-weight: 600; color: #101828; margin: 0; }

.table-wrapper {
    width: calc(100% - 46px);
    margin: 0 23px 20px;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 6px;
}
.kpi-table { width: 100%; border-collapse: collapse; white-space: nowrap; }
.kpi-table th {
    height: 48px;
    background: #f8f9fa;
    color: #344054;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    padding: 0 20px;
    border-bottom: 1px solid #d0d5dd;
}
.kpi-table td {
    height: 60px;
    padding: 0 20px;
    border-bottom: 1px solid #eef2f6;
    font-size: 14px;
    color: #101828;
    text-align: center;
    vertical-align: middle;
}
.kpi-table tbody tr:last-child td { border-bottom: 0; }

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 75px;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
.badge-hijau  { background: #e6f4e9; color: #16834f; }
.badge-biru   { background: #dbeafe; color: #1d4ed8; }
.badge-kuning { background: #fef0c7; color: #b54708; }
.badge-oranye { background: #fde8d8; color: #9a3412; }
.badge-merah  { background: #fee4e2; color: #b42318; }
.badge-abu    { background: #f2f4f7; color: #475467; }

.btn-rincian {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border: 1px solid #98a2b3;
    border-radius: 7px;
    background: #fff;
    color: #101828;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    transition: 0.2s;
}
.btn-rincian:hover { 
    background: #f8fafc; 
    border-color: #667085; 
}

.empty-data { 
    text-align: center !important; 
    height: 90px !important; 
    color: #667085 !important; 
}

.pagination-area { 
    min-height: 55px; 
    padding: 16px 23px; 
}
.paginasi { 
    display: flex; 
    align-items: center; 
    justify-content: space-between; 
    width: 100%; 
    font-size: 13px; 
    color: #667085; 
}
.paginasi-tombol { 
    display: flex; 
    align-items: center; 
    gap: 4px; 
}
.paginasi-tombol a,
.paginasi-tombol span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    background: #fff;
    color: #344054;
    font-size: 13px;
    text-decoration: none;
    transition: 0.2s;
}
.paginasi-tombol span.aktif { 
    background: #ff941c; 
    color: #fff; 
    border-color: #ff941c; 
    font-weight: 600; 
}
.paginasi-tombol span.nonaktif { 
    background: #f8f9fa; 
    color: #98a2b3; 
    cursor: not-allowed; 
}
.paginasi-tombol a:hover { 
    background: #f8fafc; 
    border-color: #98a2b3; 
}

@media (max-width: 1000px) {
    .kpi-top-grid { 
        grid-template-columns: 1fr; 
    }
    .summary-grid { 
        grid-template-columns: repeat(2, minmax(0, 1fr)); 
    }
    .kpi-filter-aksi { 
        margin-left: 0; 
    }
}

@media print {
    .kpi-filter, .btn-oranye, .btn-rincian, .pagination-area { display: none !important; }
}
.btn-rincian { font-family: inherit; cursor: pointer; }

/* Pop up rincian */
.km-kartu { 
    background: #fff; 
    border: 1px solid #d0d5dd; 
    border-radius: 16px; 
    padding: 22px 24px; 
    color: #101828; 
    max-height: 90vh; 
    overflow-y: auto; 
}
.km-kepala { 
    display: flex; 
    align-items: flex-start; 
    justify-content: space-between; 
    gap: 16px; 
    margin-bottom: 16px; 
}
.km-kepala h2 { 
    font-size: 18px; 
    font-weight: 400; 
    margin: 0 0 4px; 
}
.km-kepala p { 
    font-size: 13px; 
    color: #667085; 
    margin: 0; 
}
.km-tutup { 
    width: 32px; 
    height: 32px; 
    padding: 0; 
    border: 1px solid #d0dbea; 
    border-radius: 8px; 
    background: #fff; 
    color: #344054; 
    font-size: 18px; 
    line-height: 1; 
    cursor: pointer; 
}
.km-identitas { 
    border: 1px solid #e4e7ec; 
    border-radius: 12px; 
    padding: 14px 18px; 
    margin-bottom: 16px; 
    display: grid; 
    grid-template-columns: repeat(2, minmax(0, 1fr)); 
    gap: 12px 24px; 
    font-size: 13px; 
}
.km-identitas span { 
    display: block; 
    color: #667085; 
}
.km-ringkasan { 
    display: grid; 
    grid-template-columns: repeat(3, minmax(0, 1fr)); 
    gap: 12px; 
    margin-bottom: 16px; 
}
.km-kotak { 
    border: 1px solid #e4e7ec; 
    border-radius: 12px; 
    padding: 12px 16px; 
}
.km-angka { 
    font-size: 24px; 
    line-height: 1.2; 
}
.km-label { 
    font-size: 12px; 
    color: #667085; 
    margin-top: 2px; 
}
.km-judul { 
    font-size: 14px; 
    font-weight: 400; 
    margin: 0 0 8px; 
}
.km-tabel-bungkus { 
    border: 1px solid #d0d5dd; 
    border-radius: 6px; 
    overflow-x: auto; 
    margin-bottom: 16px; 
}
.km-tabel { 
    width: 100%; 
    border-collapse: collapse; 
    white-space: nowrap; 
}
.km-tabel th { 
    height: 42px; 
    background: #f8f9fa; 
    color: #344054; 
    font-size: 13px; 
    font-weight: 400; 
    text-align: center; 
    padding: 0 14px; 
    border-bottom: 1px solid #d0d5dd; 
}
.km-tabel td { 
    height: 44px; 
    padding: 0 14px; 
    border-bottom: 1px solid #eef2f6; 
    font-size: 13px; 
    font-weight: 400; 
    text-align: center; 
}
.km-tabel tbody tr:last-child td, .km-tabel tfoot td { 
    border-bottom: 0; 
}
.km-tabel tfoot td { 
    background: #f8f9fa; 
    font-weight: 600; 
}
.km-catatan { 
    background: #f8f9fa; 
    border: 1px solid #e4e7ec; 
    border-radius: 12px; 
    padding: 10px 16px; 
    font-size: 13px; 
    margin-bottom: 12px; 
}
.km-peringatan { 
    background: #fbe9dd; 
    color: #9a3412; 
    padding: 10px 16px; 
    border-radius: 12px; 
    font-size: 13px; 
    margin-bottom: 12px; 
}
.km-aksi { 
    display: flex; 
    justify-content: flex-end; 
    gap: 10px; 
    flex-wrap: wrap; 
    margin-top: 14px; 
}
.km-btn { 
    height: 40px; 
    padding: 0 18px; 
    border-radius: 10px; 
    font-size: 14px; 
    font-weight: 400; 
    font-family: inherit; 
    cursor: pointer; 
    border: 1px solid transparent; 
}
.km-btn-abu { 
    background: #fff; 
    color: #344054; 
    border-color: #d0d5dd; 
}
.km-btn-merah { 
    background: #fff; 
    color: #b42318; 
    border-color: #fda29b; 
}
.km-btn-oranye { 
    background: #ff941c; 
    color: #fff; 
}
.km-btn-oranye:hover { 
    background: #e58316; 
}
.km-btn[disabled] { 
    opacity: 0.5; 
    cursor: not-allowed; 
}

.kpi-grip { 
    position: absolute; 
    top: 0; 
    bottom: 0; 
    width: 14px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    cursor: ew-resize; 
    touch-action: none; 
    z-index: 2; 
}
.kpi-grip::after { 
    content: ''; 
    width: 4px; 
    height: 48px; 
    border-radius: 2px; 
    background: rgba(255, 255, 255, 0.55); 
    transition: background 0.15s; 
}
.kpi-grip:hover::after { 
    background: #fff; 
}
.kpi-grip-kiri { 
    left: -18px; 
}
.kpi-grip-kanan { 
    right: -18px; 
}
@media (max-width: 700px) { .kpi-grip { display: none; } .km-identitas, .km-ringkasan { grid-template-columns: 1fr; } }
</style>

<div id="modalRincian" onclick="if (event.target === this) tutupModalRincian()" style="display: none; position: fixed; inset: 0; background: rgba(16, 24, 40, 0.55); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div id="kotakModalRincian" style="position: relative; width: 880px; max-width: 96vw;">
        <div class="kpi-grip kpi-grip-kiri" data-sisi="kiri" title="Seret untuk mengubah lebar (klik dua kali untuk mengembalikan)"></div>
        <div id="wadahRincian"></div>
        <div class="kpi-grip kpi-grip-kanan" data-sisi="kanan" title="Seret untuk mengubah lebar (klik dua kali untuk mengembalikan)"></div>
    </div>
</div>

<script>
    const LEBAR_AWAL_RINCIAN = 880;
    const LEBAR_MIN_RINCIAN = 480;
    let seretanRincian = null;

    function terapkanLebarRincian(px) {
        const maks = window.innerWidth * 0.96;
        const lebar = Math.max(Math.min(LEBAR_MIN_RINCIAN, maks), Math.min(px, maks));
        document.getElementById('kotakModalRincian').style.width = lebar + 'px';
        return lebar;
    }

    function simpanLebarRincian(px) {
        try { localStorage.setItem('kpi-lebar-modal-px', String(Math.round(px))); } catch (e) {}
    }

    document.querySelectorAll('.kpi-grip').forEach(function (grip) {
        grip.addEventListener('pointerdown', function (e) {
            e.preventDefault();
            seretanRincian = {
                x: e.clientX,
                lebar: document.getElementById('kotakModalRincian').getBoundingClientRect().width,
                arah: grip.dataset.sisi === 'kanan' ? 1 : -1
            };
            grip.setPointerCapture(e.pointerId);
            document.body.style.userSelect = 'none';
        });
        grip.addEventListener('pointermove', function (e) {
            if (!seretanRincian) return;
            terapkanLebarRincian(seretanRincian.lebar + 2 * seretanRincian.arah * (e.clientX - seretanRincian.x));
        });
        const selesai = function () {
            if (!seretanRincian) return;
            seretanRincian = null;
            document.body.style.userSelect = '';
            simpanLebarRincian(document.getElementById('kotakModalRincian').getBoundingClientRect().width);
        };
        grip.addEventListener('pointerup', selesai);
        grip.addEventListener('pointercancel', selesai);
        grip.addEventListener('dblclick', function () {
            simpanLebarRincian(terapkanLebarRincian(LEBAR_AWAL_RINCIAN));
        });
    });

    function bukaModalRincian(idTemplate) {
        const templ = document.getElementById(idTemplate);
        if (!templ) return;
        document.getElementById('wadahRincian').innerHTML = templ.innerHTML;
        document.getElementById('modalRincian').style.display = 'flex';
        document.body.style.overflow = 'hidden';

        let tersimpan = LEBAR_AWAL_RINCIAN;
        try { tersimpan = parseInt(localStorage.getItem('kpi-lebar-modal-px'), 10) || LEBAR_AWAL_RINCIAN; } catch (e) {}
        terapkanLebarRincian(tersimpan);
    }

    function tutupModalRincian() {
        document.getElementById('modalRincian').style.display = 'none';
        document.getElementById('wadahRincian').innerHTML = '';
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') tutupModalRincian();
    });

</script>
@endif
@endsection