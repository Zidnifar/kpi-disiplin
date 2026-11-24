@php
    $isSem1 = (int) ($pilihan['semester'] ?? 1) === 1;
    $labelSemester = $isSem1 ? 'SEMESTER 1' : 'SEMESTER 2';
    $rentangBulan = $isSem1 ? 'JANUARI - JUNI' : 'JULI - DESEMBER';
    $bulanList = $isSem1
        ? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun']
        : ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    preg_match('/\b(20\d{2})\b/', (string) ($pilihan['label'] ?? ''), $cocokTahun);
    $tahunLaporan = $tahun ?? ($pilihan['tahun'] ?? ($cocokTahun[1] ?? now()->year));
    $tahunSingkat = substr((string) $tahunLaporan, -2);

    $isUnit = (bool) ($unit ?? false);
    $namaDeptUpper = \Illuminate\Support\Str::upper((string) ($namaDept ?? ''));
    $tampilDeptSubjudul = $isUnit && $namaDeptUpper !== '' && $namaDeptUpper !== 'SEMUA DEPARTEMEN';

    $subjudulKriteria = $tampilDeptSubjudul
        ? "BULAN {$rentangBulan} TAHUN {$tahunLaporan} · {$namaDeptUpper}"
        : "BULAN {$rentangBulan} TAHUN {$tahunLaporan}";

    $subjudulAkhir = $tampilDeptSubjudul
        ? "{$rentangBulan} · {$namaDeptUpper}"
        : $rentangBulan;

    $kriteriaKolom = [
        'Alpha',
        'Izin Tidak Masuk Tanpa Prosedur',
        'Pelanggaran K3',
        'SP 1',
        'SP 2',
        'SP 3',
        'SP Pertama & Terakhir',
        'Surat Teguran',
        'Sakit Tanpa Surat Dokter',
        'Terlambat Tanpa Keterangan',
    ];

    $barisLaporan = collect($data['baris'] ?? []);
@endphp

<style>
.lap-wrap {
    color: #111827;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.lap-blok {
    margin-bottom: 28px;
}
.lap-blok:last-child {
    margin-bottom: 0;
}
.lap-judul-tengah {
    text-align: center;
    margin-bottom: 12px;
}
.lap-judul-tengah h3 {
    margin: 0 0 2px;
    font-size: 15px;
    font-weight: 700;
    text-transform: uppercase;
    color: #111827;
}
.lap-judul-tengah p {
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    color: #111827;
}
.lap-meta-bar {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    font-size: 12.5px;
    color: #4b5563;
    margin-bottom: 8px;
}
.lap-scroll {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #111827;
}
.lap-grid {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.lap-grid th,
.lap-grid td {
    border: 1px solid #111827;
    padding: 6px 8px;
    vertical-align: middle;
}
.lap-grid thead th {
    background: #eef2f6;
    color: #111827;
    font-weight: 700;
    text-align: center;
}
.lap-grid td.tengah {
    text-align: center;
}
.lap-grid td.kanan {
    text-align: right;
}
.lap-grid td.kiri {
    text-align: left;
}
.lap-page-break {
    page-break-before: always;
    break-before: page;
}
@media print {
    @page {
        size: A4 landscape;
        margin: 12mm;
    }

    .lap-meta-bar,
    .no-print {
        display: none !important;
    }

    .lap-scroll {
        overflow: visible !important;
        border: none !important;
    }
}
</style>

<div class="lap-wrap">
    <div class="lap-meta-bar">
        <span><strong>Unit / Departemen:</strong> {{ $namaDept ?? '—' }}</span>
        <span><strong>Periode:</strong> {{ $pilihan['label'] ?? ($labelSemester . ' ' . $tahunLaporan) }}</span>
    </div>

    <!-- Bagian 1: Rekap Penilaian KPI Kedisiplinan -->
    <section class="lap-blok">
        <div class="lap-judul-tengah">
            <h3>REKAP PENILAIAN KPI KEDISIPLINAN {{ $labelSemester }}</h3>
            <p>{{ $subjudulKriteria }}</p>
        </div>

        <div class="lap-scroll">
            <table class="lap-grid">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 44px;">No</th>
                        <th rowspan="2">Nama Karyawan</th>
                        <th rowspan="2">Bagian</th>
                        <th colspan="{{ count($kriteriaKolom) }}">Kriteria Jumlah Penilaian Kedisiplinan Karyawan</th>
                    </tr>
                    <tr>
                        @foreach ($kriteriaKolom as $namaKriteria)
                            <th>{{ $namaKriteria }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barisLaporan as $r)
                        @php
                            $rekapKriteria = $r['kriteria'] ?? ($r['detail_kriteria'] ?? []);
                        @endphp
                        <tr>
                            <td class="tengah">{{ $loop->iteration }}</td>
                            <td class="kiri"><strong>{{ $r['karyawan']->nama_karyawan ?? '—' }}</strong></td>
                            <td class="kiri">{{ $r['karyawan']->departemen->nama_dept ?? ($namaDept ?? '—') }}</td>
                            @foreach ($kriteriaKolom as $namaKriteria)
                                @php
                                    $keySlug = \Illuminate\Support\Str::slug($namaKriteria, '_');
                                    $nilaiKriteria = $rekapKriteria[$namaKriteria]
                                        ?? ($rekapKriteria[$keySlug] ?? 0);
                                @endphp
                                <td class="tengah">{{ $nilaiKriteria }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + count($kriteriaKolom) }}" class="tengah" style="padding: 18px; color: #6b7280;">
                                Belum ada data rekap kedisiplinan untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Bagian 2: Rekap Total Penilaian Akhir KPI Individu -->
    <section class="lap-blok lap-page-break">
        <div class="lap-judul-tengah">
            <h3>REKAP TOTAL PENILAIAN AKHIR KPI INDIVIDU {{ $labelSemester }} TAHUN {{ $tahunLaporan }}</h3>
            <p>{{ $subjudulAkhir }}</p>
        </div>

        <div class="lap-scroll">
            <table class="lap-grid">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 44px;">No</th>
                        <th rowspan="2">Nama Karyawan</th>
                        <th rowspan="2">Bagian</th>
                        <th rowspan="2">Total Penilaian Akhir<br>Individu {{ ucwords(strtolower($labelSemester)) }}</th>
                        <th colspan="{{ count($bulanList) }}">PERIODE</th>
                    </tr>
                    <tr>
                        @foreach ($bulanList as $bln)
                            <th>{{ $bln }}-{{ $tahunSingkat }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barisLaporan as $r)
                        @php
                            $bulanan = $r['bulanan'] ?? ($r['periode_bulanan'] ?? []);
                            $nilaiAkhirIndividu = $r['semester'] ?? ($r['total_akhir'] ?? null);
                        @endphp
                        <tr>
                            <td class="tengah">{{ $loop->iteration }}</td>
                            <td class="kiri"><strong>{{ $r['karyawan']->nama_karyawan ?? '—' }}</strong></td>
                            <td class="kiri">{{ $r['karyawan']->departemen->nama_dept ?? ($namaDept ?? '—') }}</td>
                            <td class="kanan"><strong>{{ \App\Support\Format::nilai($nilaiAkhirIndividu) }}</strong></td>
                            @foreach ($bulanList as $idxBln => $bln)
                                @php
                                    $angkaBln = is_array($bulanan)
                                        ? ($bulanan[$idxBln + 1] ?? ($bulanan[strtolower($bln)] ?? ($bulanan[$bln . '-' . $tahunSingkat] ?? null)))
                                        : null;
                                @endphp
                                <td class="kanan">{{ \App\Support\Format::nilai($angkaBln) }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + count($bulanList) }}" class="tengah" style="padding: 18px; color: #6b7280;">
                                Belum ada data rekap penilaian akhir individu untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>