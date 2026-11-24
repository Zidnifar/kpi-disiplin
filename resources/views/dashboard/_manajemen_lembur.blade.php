@php
    $koleksiMenunggu = collect($menunggu ?? []);
    $daftarStatistik = collect($statistik ?? []);

    $dataGrafik = collect($dataGrafik ?? ($jamPerUnit ?? []))->map(function ($baris) {
        if (is_array($baris)) {
            $labelItem = (string) ($baris['label'] ?? ($baris['unit'] ?? ($baris['nama_dept'] ?? 'Item')));
            $nilaiItem = (float) ($baris['nilai'] ?? ($baris['jam'] ?? 0));
        } elseif (is_object($baris)) {
            $labelItem = (string) ($baris->label ?? ($baris->unit ?? ($baris->nama_dept ?? 'Item')));
            $nilaiItem = (float) ($baris->nilai ?? ($baris->jam ?? 0));
        } else {
            $labelItem = 'Item';
            $nilaiItem = 0.0;
        }

        return [
            'label' => $labelItem,
            'nilai' => max(0.0, $nilaiItem),
        ];
    });

    $maksNilaiGrafik = max(1.0, (float) ($dataGrafik->max('nilai') ?? 1.0));
    $skalaYGrid = max(60, (int) (ceil($maksNilaiGrafik / 20) * 20));

    $ruteIndexTujuan = $ruteIndex ?? 'lembur.putuskan.index';
    $ruteDetailTujuan = $ruteDetail ?? 'lembur.putuskan.show';

    $urlPintasanLembur = \Illuminate\Support\Facades\Route::has($ruteIndexTujuan)
        ? route($ruteIndexTujuan)
        : '#';
    $urlPintasanKpi = \Illuminate\Support\Facades\Route::has('saya.kpi')
        ? route('saya.kpi')
        : '#';
    $urlPintasanKehadiran = \Illuminate\Support\Facades\Route::has('saya.konfirmasi')
        ? route('saya.konfirmasi')
        : '#';

    $jumlahMenungguKartu = (int) ($daftarStatistik->first()['nilai'] ?? $koleksiMenunggu->count());
@endphp

<style>
.mgmt-dash {
    width: 100%;
    padding: 0 8px 28px;
    color: #101828;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.mgmt-dash * {
    box-sizing: border-box;
}
.mgmt-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
}

.mgmt-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 20px;
}
.mgmt-stat-card {
    min-height: 98px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    min-width: 0;
}
.mgmt-stat-num {
    font-size: clamp(24px, 2.2vw, 32px);
    font-weight: 700;
    line-height: 1.15;
    color: #101828;
    margin-bottom: 6px;
}
.mgmt-stat-lbl {
    font-size: 13px;
    color: #475467;
}
.mgmt-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: #eef2f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.mgmt-stat-icon svg {
    width: 21px;
    height: 21px;
    stroke: #101828;
    stroke-width: 1.8;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.mgmt-main-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}
.mgmt-panel {
    padding: 22px 24px;
    min-width: 0;
}
.mgmt-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}
.mgmt-panel-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #101828;
}

/* Kotak Lihat Semua warna oren */
.mgmt-lihat-semua {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 34px;
    padding: 0 14px;
    border-radius: 9px;
    border: 1px solid #ff941c;
    background: #ff941c;
    color: #fff;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.18s ease, border-color 0.18s ease;
}
.mgmt-lihat-semua:hover {
    background: #e8850f;
    border-color: #e8850f;
    color: #fff;
}

.mgmt-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
}
.mgmt-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 460px;
}
.mgmt-table thead {
    background: #f8f9fa;
}
.mgmt-table th {
    height: 44px;
    padding: 0 14px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #344054;
    border-bottom: 1px solid #d0d5dd;
    white-space: nowrap;
}
.mgmt-table th.aksi-col,
.mgmt-table td.aksi-col {
    text-align: right;
}
.mgmt-table td {
    height: 54px;
    padding: 0 14px;
    font-size: 13.5px;
    color: #101828;
    border-bottom: 1px solid #eef2f6;
    vertical-align: middle;
    white-space: nowrap;
}
.mgmt-table tbody tr:last-child td {
    border-bottom: 0;
}
.mgmt-btn-oranye {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 32px;
    padding: 0 14px;
    border-radius: 8px;
    background: #ff941c;
    color: #fff;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    border: 0;
    transition: background 0.18s ease;
}
.mgmt-btn-oranye:hover {
    background: #e8850f;
}
.mgmt-empty {
    text-align: center !important;
    padding: 28px 14px !important;
    color: #667085 !important;
    font-size: 13.5px;
}

.mgmt-chart-box {
    position: relative;
    height: 220px;
    padding: 26px 10px 30px 34px;
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    gap: 14px;
    border-bottom: 1px solid #d0d5dd;
    background-image: repeating-linear-gradient(
        to top,
        #e5e7eb 0px,
        #e5e7eb 1px,
        transparent 1px,
        transparent 64px
    );
    background-position: 34px 26px;
    background-size: calc(100% - 44px) 192px;
    background-repeat: no-repeat;
}
.mgmt-tick {
    position: absolute;
    left: 0;
    font-size: 11px;
    color: #667085;
    transform: translateY(-50%);
}
.mgmt-tick-atas {
    top: 26px;
}
.mgmt-tick-tengah {
    top: 90px;
}
.mgmt-tick-bawah {
    top: 154px;
}
.mgmt-tick-nol {
    bottom: 6px;
    transform: none;
}
.mgmt-bar-col {
    flex: 1 1 0;
    max-width: 78px;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    position: relative;
    min-width: 0;
}
.mgmt-bar-val {
    font-size: 12px;
    font-weight: 700;
    color: #101828;
    margin-bottom: 6px;
    white-space: nowrap;
}
.mgmt-bar-track {
    width: 46px;
    max-width: 100%;
    flex: 1 1 auto;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}
.mgmt-bar-fill {
    width: 100%;
    min-height: 4px;
    border-radius: 10px 10px 0 0;
    background: #ffcfac;
    transition: height 0.25s ease;
}
.mgmt-bar-fill.terakhir {
    background: #ff941c;
}
.mgmt-bar-label {
    position: absolute;
    bottom: -25px;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 12px;
    color: #475467;
}

.mgmt-shortcuts-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
}
.mgmt-shortcut-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    text-decoration: none;
    color: inherit;
    transition: border-color 0.18s ease, transform 0.18s ease;
    min-width: 0;
}
.mgmt-shortcut-card:hover {
    border-color: #98a2b3;
    transform: translateY(-1px);
}
.mgmt-shortcut-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #eef2f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.mgmt-shortcut-icon svg {
    width: 20px;
    height: 20px;
    stroke: #101828;
    stroke-width: 1.8;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.mgmt-shortcut-text {
    min-width: 0;
}
.mgmt-shortcut-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #101828;
    margin-bottom: 3px;
}
.mgmt-shortcut-sub {
    font-size: 12.5px;
    color: #667085;
}

@media (max-width: 1100px) {
    .mgmt-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .mgmt-main-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 760px) {
    .mgmt-shortcuts-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 600px) {
    .mgmt-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="mgmt-dash">
    <section class="mgmt-stats-grid">
        @foreach ($daftarStatistik as $kartuItem)
            <div class="mgmt-card mgmt-stat-card">
                <div>
                    <div class="mgmt-stat-num">{{ $kartuItem['nilai'] ?? 0 }}</div>
                    <div class="mgmt-stat-lbl">{{ $kartuItem['label'] ?? '-' }}</div>
                </div>
                <div class="mgmt-stat-icon">
                    @if (($kartuItem['ikon'] ?? '') === 'check')
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>
                    @elseif (($kartuItem['ikon'] ?? '') === 'x')
                        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    @elseif (($kartuItem['ikon'] ?? '') === 'doc')
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="13" y2="17"/></svg>
                    @elseif (($kartuItem['ikon'] ?? '') === 'chart')
                        <svg viewBox="0 0 24 24"><path d="M4 20V14"/><path d="M12 20V9"/><path d="M20 20V4"/></svg>
                    @else
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg>
                    @endif
                </div>
            </div>
        @endforeach
    </section>

    <section class="mgmt-main-grid">
        <div class="mgmt-card mgmt-panel">
            <div class="mgmt-panel-head">
                <h2 class="mgmt-panel-title">{{ $judulAntrean ?? 'Menunggu keputusan' }}</h2>
                <a href="{{ $urlPintasanLembur }}" class="mgmt-lihat-semua">Lihat Semua</a>
            </div>

            <div class="mgmt-table-wrap">
                <table class="mgmt-table">
                    <thead>
                        <tr>
                            <th>No. Pengajuan</th>
                            <th>Unit</th>
                            <th>Tanggal</th>
                            <th class="aksi-col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($koleksiMenunggu as $r)
                        @php
                            $urlDetail = \Illuminate\Support\Facades\Route::has($ruteDetailTujuan)
                                ? route($ruteDetailTujuan, $r)
                                : '#';
                        @endphp
                        <tr>
                            <td><strong>{{ $r->no_pengajuan }}</strong></td>
                            <td>{{ $r->departemen->nama_dept ?? '—' }}</td>
                            <td>{{ \App\Support\Format::tanggal($r->tanggal_lembur) }}</td>
                            <td class="aksi-col">
                                <a class="mgmt-btn-oranye" href="{{ $urlDetail }}">{{ $labelAksi ?? 'Buka' }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="mgmt-empty">Tidak ada pengajuan yang menunggu.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mgmt-card mgmt-panel">
            <div class="mgmt-panel-head">
                <h2 class="mgmt-panel-title">{{ $judulGrafik ?? 'Jam Lembur per Unit' }}</h2>
            </div>

            @if ($dataGrafik->isEmpty())
                <div class="mgmt-empty" style="padding: 64px 12px !important;">Belum ada data grafik lembur.</div>
            @else
                <div class="mgmt-chart-box">
                    <span class="mgmt-tick mgmt-tick-atas">{{ $skalaYGrid }}</span>
                    <span class="mgmt-tick mgmt-tick-tengah">{{ (int) round(($skalaYGrid * 2) / 3) }}</span>
                    <span class="mgmt-tick mgmt-tick-bawah">{{ (int) round($skalaYGrid / 3) }}</span>
                    <span class="mgmt-tick mgmt-tick-nol">0</span>

                    @foreach ($dataGrafik as $kolomItem)
                        @php
                            $nilaiItem = $kolomItem['nilai'];
                            $persenTinggi = $nilaiItem > 0
                                ? max(8, min(100, ($nilaiItem / $skalaYGrid) * 100))
                                : 2;
                            $teksAngka = rtrim(rtrim(number_format($nilaiItem, 1, '.', ''), '0'), '.');
                        @endphp
                        <div class="mgmt-bar-col" title="{{ $kolomItem['label'] }}: {{ $teksAngka }} jam">
                            <div class="mgmt-bar-val">{{ $teksAngka !== '' ? $teksAngka : '0' }}</div>
                            <div class="mgmt-bar-track">
                                <div class="mgmt-bar-fill {{ $loop->last ? 'terakhir' : '' }}" style="height: {{ $persenTinggi }}%;"></div>
                            </div>
                            <div class="mgmt-bar-label">{{ $kolomItem['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="mgmt-shortcuts-grid">
        <a href="{{ $urlPintasanLembur }}" class="mgmt-card mgmt-shortcut-card">
            <div class="mgmt-shortcut-icon">
                @if (($ikonPintasanLembur ?? '') === 'check')
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>
                @elseif (($ikonPintasanLembur ?? '') === 'clock')
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg>
                @else
                    <svg viewBox="0 0 24 24"><rect x="6" y="4" width="12" height="16" rx="2"/><path d="M9 4h6v2H9z"/><path d="M9 11h6"/><path d="M9 15h4"/></svg>
                @endif
            </div>
            <div class="mgmt-shortcut-text">
                <div class="mgmt-shortcut-title">{{ $judulPintasanLembur ?? 'Rencana Lembur' }}</div>
                <div class="mgmt-shortcut-sub">{{ $jumlahMenungguKartu }} menunggu</div>
            </div>
        </a>

        <a href="{{ $urlPintasanKpi }}" class="mgmt-card mgmt-shortcut-card">
            <div class="mgmt-shortcut-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M4 20V14"/>
                    <path d="M12 20V9"/>
                    <path d="M20 20V4"/>
                </svg>
            </div>
            <div class="mgmt-shortcut-text">
                <div class="mgmt-shortcut-title">Nilai KPI Saya</div>
                <div class="mgmt-shortcut-sub">{{ $labelPeriodeKpi ?? 'Periode Aktif' }}</div>
            </div>
        </a>

        <a href="{{ $urlPintasanKehadiran }}" class="mgmt-card mgmt-shortcut-card">
            <div class="mgmt-shortcut-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m8.5 12.5 2.5 2.5 4.5-5"/>
                </svg>
            </div>
            <div class="mgmt-shortcut-text">
                <div class="mgmt-shortcut-title">Konfirmasi Kehadiran</div>
                <div class="mgmt-shortcut-sub">{{ (int) ($jumlahKonfirmasi ?? 0) }} data menunggu</div>
            </div>
        </a>
    </section>
</div>