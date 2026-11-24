@extends('layouts.app')

@section('title', 'Penilaian KPI')
@section('judul', 'Penilaian KPI')

@section('isi')
<div class="pn-page">
    @if (! $periode)
        <div class="pn-card">
            <p class="pn-muted">Belum ada periode penilaian. Tambahkan periode lebih dulu di menu Pengaturan KPI → <a href="{{ route('admin.kpi.periode.create') }}" class="pn-link">Periode KPI</a>.</p>
        </div>
    @else
        <!-- Kartu Periode & Status Kesiapan -->
        <div class="pn-card">
            <h2 class="pn-title">Periode Penilaian</h2>

            <div class="pn-filter-row">
                <form method="GET" class="pn-select-form">
                    <select name="periode" onchange="this.form.submit()" aria-label="Periode penilaian" class="pn-select">
                        @foreach ($periodeList as $p)
                            <option value="{{ $p->id_periode }}" @selected($p->id_periode === $periode->id_periode)>{{ $p->nama_periode }}</option>
                        @endforeach
                    </select>
                </form>

                <form id="form-hitung" method="POST" action="{{ route('admin.kpi.penilaian.hitung') }}" data-konfirmasi="Jalankan perhitungan KPI {{ $periode->nama_periode }}? Hasil yang belum tervalidasi akan dihitung ulang." class="pn-hitung-form">
                    @csrf
                    <input type="hidden" name="periode" value="{{ $periode->id_periode }}">
                    @if ($kesiapan['belum'] > 0)
                        <label class="pn-paksa">
                            <input type="checkbox" name="paksa" value="1"> Hitung tetap meski ada data belum dikonfirmasi
                        </label>
                    @endif
                    <button type="submit" class="pn-btn">Jalankan perhitungan</button>
                </form>
            </div>

            <!-- Kesiapan data -->
            <table class="pn-kesiapan">
                <tbody>
                    <tr>
                        <td>Data absensi periode tersedia</td>
                        <td>
                            @if ($kesiapan['absensi'] > 0)
                                <span class="pn-badge pn-hijau">Siap</span>
                            @else
                                <span class="pn-badge pn-merah">Belum ada data</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Indikator dan aturan perhitungan</td>
                        <td>
                            <span class="pn-badge {{ $kesiapan['aturan'] ? 'pn-hijau' : 'pn-merah' }}">{{ $kesiapan['aturan'] ? 'Siap' : 'Belum siap' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Data sudah dikonfirmasi</td>
                        <td>
                            @if ($kesiapan['belum'] > 0)
                                <span class="pn-badge pn-oranye">{{ $kesiapan['belum'] }} data belum dikonfirmasi</span>
                            @else
                                <span class="pn-badge pn-hijau">Siap</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        @if ($kesiapan['absensi'] === 0)
            <div class="pn-alert">
                Belum ada data absensi pada periode ini. <a href="{{ route('admin.absensi.impor') }}" class="pn-alert-link">Impor data absensi</a> terlebih dahulu.
            </div>
        @elseif ($kesiapan['belum'] > 0)
            <div class="pn-alert">
                Data belum mencukupi untuk {{ $kesiapan['karyawanBelum'] }} karyawan. Konfirmasi kehadiran terlebih dahulu.
                <a href="{{ route('admin.kesalahan.index', ['bulan' => $periode->tanggal_mulai->format('Y-m')]) }}" class="pn-alert-link">Periksa kesalahan absensi</a>
            </div>
        @endif

        <!-- Hasil dan rincian perhitungan -->
        <div class="pn-card pn-card-tabel">
            <h2 class="pn-title" style="padding: 20px 24px 0;">Hasil dan rincian perhitungan</h2>

            <div class="pn-table-wrapper">
                <table class="pn-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Karyawan</th>
                            @foreach ($bulan as $nama)
                                <th>{{ $nama }}</th>
                            @endforeach
                            <th>Nilai akhir</th>
                            <th>Kategori</th>
                            <th style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($baris as $index => $r)
                        @php
                            $nomorUrut = method_exists($baris, 'firstItem') && $baris->firstItem() ? $baris->firstItem() + $index : $index + 1;
                            $predikat = $r['predikat'] ?? null;
                            $kelasPredikat = match (strtolower((string) $predikat)) {
                                'sangat baik' => 'pn-hijau',
                                'baik'        => 'pn-biru',
                                'cukup'       => 'pn-oranye',
                                'kurang'      => 'pn-merah',
                                default       => 'pn-abu',
                            };
                        @endphp
                        <tr>
                            <td class="pn-redup">{{ $nomorUrut }}</td>
                            <td>{{ $r['karyawan']->nama_karyawan }}</td>
                            @foreach ($bulan as $nomor => $nama)
                                @php $poin = $r['bulan'][$nomor] ?? 0; @endphp
                                <td>{{ $poin > 0 ? '− ' . $poin : '0' }}</td>
                            @endforeach
                            <td>{{ \App\Support\Format::nilai($r['nilai']) }}</td>
                            <td>
                                @if ($predikat)
                                    <span class="pn-badge pn-kecil {{ $kelasPredikat }}">{{ $predikat }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <button type="button" onclick="bukaModalDetail('detail-{{ $r['karyawan']->id_karyawan }}')" class="pn-btn-ikon" title="Lihat detail penilaian">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($bulan) + 5 }}" class="pn-kosong">Belum ada karyawan aktif.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

<!-- Isi pop up detail per karyawan (tersembunyi, dibuka lewat tombol mata) -->
            @foreach ($baris as $r)
                @php
                    $predikat = $r['predikat'] ?? null;
                    $warnaPredikat = match (strtolower((string) $predikat)) {
                        'sangat baik' => ['#e6f4e9', '#16834f'],
                        'baik'        => ['#dbeafe', '#1d4ed8'],
                        'cukup'       => ['#fde8d8', '#9a3412'],
                        'kurang'      => ['#fee4e2', '#b42318'],
                        default       => ['#f2f4f7', '#475467'],
                    };
                    $hasilKpi = $r['hasil'] ?? null;
                    $statusTeks = $hasilKpi ? ($hasilKpi->status_validasi === 'Tervalidasi' ? 'Disetujui' : $hasilKpi->status_validasi) : 'Belum dihitung';
                    $warnaStatus = $hasilKpi && $hasilKpi->status_validasi === 'Tervalidasi' ? ['#dbeafe', '#1d4ed8'] : ['#fde8d8', '#9a3412'];
                    $adaRincian = isset($r['rincian']);   // opsional: ['nomor_bulan' => ['hadir' => 0, 'terlambat' => 0, 'belum' => 0]]
                    $thS = 'height: 42px; background: #f8f9fa; color: #344054; font-size: 13px; font-weight: 400; text-align: center; padding: 0 14px; border-bottom: 1px solid #d0d5dd;';
                    $tdS = 'height: 44px; padding: 0 14px; border-bottom: 1px solid #eef2f6; font-size: 13px; font-weight: 400; color: #101828; text-align: center;';
                @endphp
                <template id="detail-{{ $r['karyawan']->id_karyawan }}">
                    <div style="background: #fff; border-radius: 16px; border: 1px solid #d0d5dd; padding: 22px 24px; color: #101828; max-height: 90vh; overflow-y: auto;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <h2 style="font-size: 18px; font-weight: 400; margin: 0 0 4px;">Detail Penilaian</h2>
                                <p style="font-size: 13px; color: #667085; margin: 0;">{{ $periode->nama_periode }}</p>
                            </div>
                            <button type="button" onclick="tutupModalDetail()" aria-label="Tutup" style="width: 32px; height: 32px; padding: 0; border: 1px solid #d0dbea; border-radius: 8px; background: #fff; color: #344054; font-size: 18px; line-height: 1; cursor: pointer;">×</button>
                        </div>

                        <div style="border: 1px solid #e4e7ec; border-radius: 12px; padding: 14px 18px; margin-bottom: 16px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 24px; font-size: 13px;">
                            <div><span style="color: #667085;">Nama</span><br>{{ $r['karyawan']->nama_karyawan }}</div>
                            <div><span style="color: #667085;">NIP</span><br>{{ $r['karyawan']->nip ?? '—' }}</div>
                            <div><span style="color: #667085;">Departemen</span><br>{{ $r['karyawan']->departemen->nama_dept ?? '—' }}</div>
                            <div><span style="color: #667085;">Jabatan</span><br>{{ $r['karyawan']->jabatan ?? '—' }}</div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px;">
                            <div style="border: 1px solid #e4e7ec; border-radius: 12px; padding: 12px 16px;">
                                <div style="font-size: 24px; line-height: 1.2;">{{ \App\Support\Format::nilai($r['nilai']) }}</div>
                                <div style="font-size: 12px; color: #667085; margin-top: 2px;">Nilai akhir</div>
                            </div>
                            <div style="border: 1px solid #e4e7ec; border-radius: 12px; padding: 12px 16px;">
                                <div style="margin-top: 4px;">
                                    @if ($predikat)
                                        <span style="display: inline-flex; padding: 4px 12px; border-radius: 20px; font-size: 12px; background: {{ $warnaPredikat[0] }}; color: {{ $warnaPredikat[1] }};">{{ $predikat }}</span>
                                    @else
                                        —
                                    @endif
                                </div>
                                <div style="font-size: 12px; color: #667085; margin-top: 8px;">Kategori</div>
                            </div>
                            <div style="border: 1px solid #e4e7ec; border-radius: 12px; padding: 12px 16px;">
                                <div style="margin-top: 4px;">
                                    <span style="display: inline-flex; padding: 4px 12px; border-radius: 20px; font-size: 12px; background: {{ $warnaStatus[0] }}; color: {{ $warnaStatus[1] }};">{{ $statusTeks }}</span>
                                </div>
                                <div style="font-size: 12px; color: #667085; margin-top: 8px;">Status</div>
                            </div>
                        </div>

                        <h3 style="font-size: 14px; font-weight: 400; margin: 0 0 8px;">Rincian per bulan</h3>
                        <div style="border: 1px solid #d0d5dd; border-radius: 6px; overflow-x: auto; margin-bottom: 16px;">
                            <table style="width: 100%; border-collapse: collapse; white-space: nowrap;">
                                <thead>
                                    <tr>
                                        <th style="{{ $thS }}">Bulan</th>
                                        @if ($adaRincian)
                                            <th style="{{ $thS }}">Hadir</th>
                                            <th style="{{ $thS }}">Terlambat</th>
                                            <th style="{{ $thS }}">Belum dikonfirmasi</th>
                                        @endif
                                        <th style="{{ $thS }}">Pengurang poin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bulan as $nomor => $nama)
                                        @php
                                            $poin = $r['bulan'][$nomor] ?? 0;
                                            $rb = $r['rincian'][$nomor] ?? ['hadir' => 0, 'terlambat' => 0, 'belum' => 0];
                                        @endphp
                                        <tr>
                                            <td style="{{ $tdS }}">{{ $nama }}</td>
                                            @if ($adaRincian)
                                                <td style="{{ $tdS }}">{{ $rb['hadir'] }}</td>
                                                <td style="{{ $tdS }}">{{ $rb['terlambat'] }}</td>
                                                <td style="{{ $tdS }}">{{ $rb['belum'] }}</td>
                                            @endif
                                            <td style="{{ $tdS }}">{{ $poin > 0 ? '− ' . $poin : '0' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @php $detailPoin = $r['rincian_poin'] ?? null; @endphp
                        @if ($detailPoin !== null)
                            <h3 style="font-size: 14px; font-weight: 400; margin: 0 0 4px;">Rincian pengurangan poin</h3>
                            <p style="font-size: 12px; color: #667085; margin: 0 0 10px;">Nilai awal {{ \App\Domain\Kpi\KpiCalculator::NILAI_AWAL }} dikurangi total pengurang {{ collect($r['bulan'] ?? [])->sum() }} poin.</p>
                            @foreach ($bulan as $nomor => $nama)
                                @php
                                    $itemPoin = $detailPoin[$nomor] ?? [];
                                    $totalBulan = collect($itemPoin)->sum('poin');
                                @endphp
                                <div style="border: 1px solid #e4e7ec; border-radius: 12px; margin-bottom: 10px; overflow: hidden;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 16px; background: #f8f9fa; font-size: 13px; color: #344054;">
                                        <span>{{ $nama }}</span>
                                        <span>{{ $totalBulan > 0 ? 'Total − ' . $totalBulan . ' poin' : 'Tidak ada pengurangan' }}</span>
                                    </div>
                                    @foreach ($itemPoin as $it)
                                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 16px; border-top: 1px solid #eef2f6; font-size: 13px;">
                                            <div>
                                                <div>{{ $it['keterangan'] }}</div>
                                                @if (! empty($it['tanggal']))
                                                    <div style="font-size: 12px; color: #667085;">{{ \App\Support\Format::tanggal($it['tanggal']) }}</div>
                                                @endif
                                            </div>
                                            <span style="color: #b42318; white-space: nowrap;">− {{ $it['poin'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                            <div style="margin-bottom: 16px;"></div>
                        @endif

                        <div style="display: flex; justify-content: flex-end; gap: 10px;">
                            @if ($hasilKpi)
                                <a href="{{ route('admin.kpi.hasil.show', $hasilKpi) }}" style="height: 40px; padding: 0 18px; border: 1px solid #d0d5dd; border-radius: 10px; background: #fff; color: #344054; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center;">Buka di Hasil KPI</a>
                            @endif
                            <button type="button" onclick="tutupModalDetail()" style="height: 40px; padding: 0 20px; border: 0; border-radius: 10px; background: #ff941c; color: #fff; font-size: 14px; cursor: pointer;">Tutup</button>
                        </div>
                    </div>
                </template>
            @endforeach

            <!-- Paginasi -->
            <div class="pn-paginasi">
                <div>
                    @if (method_exists($baris, 'total') && $baris->total() > 0)
                        Menampilkan {{ $baris->firstItem() }}–{{ $baris->lastItem() }} dari {{ $baris->total() }} data
                    @else
                        Tidak ada data
                    @endif
                </div>

                @if (method_exists($baris, 'hasPages') && $baris->hasPages())
                    <nav class="pn-paginasi-tombol" role="navigation" aria-label="Halaman">
                        @if ($baris->onFirstPage())
                            <span class="nonaktif">‹</span>
                        @else
                            <a href="{{ $baris->previousPageUrl() }}" rel="prev">‹</a>
                        @endif

                        @foreach ($baris->linkCollection() as $link)
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

                        @if ($baris->hasMorePages())
                            <a href="{{ $baris->nextPageUrl() }}" rel="next">›</a>
                        @else
                            <span class="nonaktif">›</span>
                        @endif
                    </nav>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Pop up detail penilaian (lebar bisa diseret dari tepi kiri/kanan) -->
<div id="modalDetailPenilaian" onclick="if (event.target === this) tutupModalDetail()" style="display: none; position: fixed; inset: 0; background: rgba(16, 24, 40, 0.55); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div id="kotakModal" style="position: relative; width: 880px; max-width: 96vw;">
        <div class="pn-grip pn-grip-kiri" data-sisi="kiri" title="Seret untuk mengubah lebar (klik dua kali untuk mengembalikan)"></div>
        <div id="wadahKontenShow"></div>
        <div class="pn-grip pn-grip-kanan" data-sisi="kanan" title="Seret untuk mengubah lebar (klik dua kali untuk mengembalikan)"></div>
    </div>
</div>

<script>
    const LEBAR_AWAL = 880;   // lebar bawaan pop up (px)
    const LEBAR_MIN = 480;    // lebar terkecil saat diseret (px)
    let seretan = null;

    function terapkanLebar(px) {
        const maks = window.innerWidth * 0.96;
        const lebar = Math.max(Math.min(LEBAR_MIN, maks), Math.min(px, maks));
        document.getElementById('kotakModal').style.width = lebar + 'px';
        return lebar;
    }

    function simpanLebar(px) {
        try { localStorage.setItem('pn-lebar-modal-px', String(Math.round(px))); } catch (e) {}
    }

    document.querySelectorAll('.pn-grip').forEach(function (grip) {
        grip.addEventListener('pointerdown', function (e) {
            e.preventDefault();
            seretan = {
                x: e.clientX,
                lebar: document.getElementById('kotakModal').getBoundingClientRect().width,
                arah: grip.dataset.sisi === 'kanan' ? 1 : -1
            };
            grip.setPointerCapture(e.pointerId);
            document.body.style.userSelect = 'none';
        });

        grip.addEventListener('pointermove', function (e) {
            if (!seretan) return;
            // pop up berada di tengah layar, jadi kedua sisinya bergerak bersamaan (x2)
            terapkanLebar(seretan.lebar + 2 * seretan.arah * (e.clientX - seretan.x));
        });

        const selesai = function () {
            if (!seretan) return;
            seretan = null;
            document.body.style.userSelect = '';
            simpanLebar(document.getElementById('kotakModal').getBoundingClientRect().width);
        };
        grip.addEventListener('pointerup', selesai);
        grip.addEventListener('pointercancel', selesai);

        grip.addEventListener('dblclick', function () {
            simpanLebar(terapkanLebar(LEBAR_AWAL));
        });
    });

    function bukaModalDetail(idTemplate) {
        const templ = document.getElementById(idTemplate);
        if (!templ) return;
        document.getElementById('wadahKontenShow').innerHTML = templ.innerHTML;
        document.getElementById('modalDetailPenilaian').style.display = 'flex';
        document.body.style.overflow = 'hidden';

        let tersimpan = LEBAR_AWAL;
        try { tersimpan = parseInt(localStorage.getItem('pn-lebar-modal-px'), 10) || LEBAR_AWAL; } catch (e) {}
        terapkanLebar(tersimpan);
    }

    function tutupModalDetail() {
        document.getElementById('modalDetailPenilaian').style.display = 'none';
        document.getElementById('wadahKontenShow').innerHTML = '';
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') tutupModalDetail();
    });
</script>

<style>
.pn-page { width: 100%; padding: 0 8px 24px; color: #101828; }

.pn-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 20px;
    padding: 22px 24px;
    margin-bottom: 20px;
}
.pn-card-tabel { padding: 0; overflow: hidden; margin-bottom: 0; }

.pn-title { font-size: 18px; font-weight: 400; color: #101828; margin: 0 0 16px; }
.pn-muted { color: #667085; font-size: 14px; margin: 0; }
.pn-link { color: #ff941c; text-decoration: none; }

.pn-filter-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.pn-select-form { width: 220px; }
.pn-select {
    height: 40px;
    width: 100%;
    border: 1px solid #d0dbea;
    border-radius: 10px;
    padding: 0 36px 0 14px;
    background: #fff;
    color: #101828;
    font-size: 14px;
    font-weight: 400;
    outline: none;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
}
.pn-hitung-form { display: flex; align-items: center; gap: 14px; }
.pn-paksa { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #344054; cursor: pointer; }
.pn-paksa input { width: 15px; height: 15px; accent-color: #ff941c; cursor: pointer; }
.pn-btn {
    height: 40px;
    padding: 0 20px;
    border: 0;
    border-radius: 10px;
    background: #ff941c;
    color: #fff;
    font-size: 14px;
    font-weight: 400;
    cursor: pointer;
    transition: background 0.2s;
}
.pn-btn:hover { background: #e58316; }

/* Tabel kesiapan: lebar terbatas, label kiri + badge kanan */
.pn-kesiapan {
    width: 100%;
    max-width: 820px;
    border-collapse: collapse;
    border: 1px solid #e4e7ec;
}
.pn-kesiapan td {
    height: 34px;
    padding: 0 18px;
    border-bottom: 1px solid #e4e7ec;
    font-size: 13px;
    font-weight: 400;
    color: #101828;
}
.pn-kesiapan tr:last-child td { border-bottom: 0; }
.pn-kesiapan td:last-child { text-align: right; }   /* badge rata kanan tabel */

.pn-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 400;
    white-space: nowrap;
}
.pn-badge.pn-kecil { padding: 3px 12px; font-size: 11px; }
.pn-hijau  { background: #e6f4e9; color: #16834f; }
.pn-biru   { background: #dbeafe; color: #1d4ed8; }
.pn-oranye { background: #fde8d8; color: #9a3412; }
.pn-merah  { background: #fee4e2; color: #b42318; }
.pn-abu    { background: #f2f4f7; color: #475467; }

.pn-alert {
    background: #fbe9dd;
    color: #9a3412;
    padding: 12px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 400;
}
.pn-alert-link { color: #9a3412; text-decoration: underline; margin-left: 4px; }

.pn-table-wrapper {
    width: calc(100% - 48px);
    margin: 16px 24px;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 6px;
}
.pn-table { width: 100%; border-collapse: collapse; white-space: nowrap; }
.pn-table th {
    height: 46px;
    background: #f8f9fa;
    color: #344054;
    font-size: 13px;
    font-weight: 400;
    text-align: center;
    padding: 0 16px;
    border-bottom: 1px solid #d0d5dd;
}
.pn-table td {
    height: 50px;
    padding: 0 16px;
    border-bottom: 1px solid #eef2f6;
    font-size: 13px;
    font-weight: 400;
    color: #101828;
    text-align: center;
    vertical-align: middle;
}
.pn-table tbody tr:last-child td { border-bottom: 0; }
.pn-redup { color: #667085 !important; }
.pn-kosong { height: 80px !important; color: #667085 !important; }

.pn-btn-ikon {
    padding: 0;
    width: 32px;
    height: 32px;
    box-sizing: border-box;
    border: 1px solid #d0dbea;
    border-radius: 8px;
    background: #fff;
    color: #344054;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
}
.pn-btn-ikon svg { width: 16px; height: 16px; }
.pn-btn-ikon:hover { background: #f8fafc; border-color: #98a2b3; }

.pn-paginasi {
    padding: 0 24px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
    color: #667085;
}
.pn-paginasi-tombol { display: flex; align-items: center; gap: 4px; }
.pn-paginasi-tombol a,
.pn-paginasi-tombol span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 30px;
    padding: 0 8px;
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    background: #fff;
    color: #344054;
    font-size: 13px;
    text-decoration: none;
}
.pn-paginasi-tombol span.aktif { background: #ff941c; border-color: #ff941c; color: #fff; }
.pn-paginasi-tombol span.nonaktif { background: #f8f9fa; color: #98a2b3; cursor: not-allowed; }
.pn-paginasi-tombol a:hover { background: #f8fafc; border-color: #98a2b3; }
/* Pegangan untuk menyeret lebar pop up */
.pn-grip {
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
.pn-grip::after {
    content: '';
    width: 4px;
    height: 48px;
    border-radius: 2px;
    background: rgba(255, 255, 255, 0.55);
    transition: background 0.15s;
}
.pn-grip:hover::after { background: #fff; }
.pn-grip-kiri { left: -18px; }
.pn-grip-kanan { right: -18px; }
@media (max-width: 700px) { .pn-grip { display: none; } }
</style>
@endsection