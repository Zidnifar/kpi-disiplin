@extends('layouts.app')

@section('judul', 'Rekap Kehadiran')

@section('isi')
@php
    $opsiBulan = collect(range(0, 12))->map(fn ($i) => now()->startOfMonth()->subMonths($i)->format('Y-m'));
    if (! $opsiBulan->contains($bulan)) {
        $opsiBulan->push($bulan);
    }
    $opsiBulan = $opsiBulan->sortDesc()->values();

    $daftar->appends(request()->query());

    $kelasStatus = fn ($teks) => match (strtolower((string) $teks)) {
        'hadir' => 'rk-hijau',
        'terlambat' => 'rk-oranye',
        'perlu dicek', 'bermasalah', 'alpa' => 'rk-merah',
        'izin', 'sakit', 'izin / sakit' => 'rk-biru',
        default => 'rk-abu',
    };

    $kelasKonfir = function ($teks) {
        $t = strtolower((string) $teks);

        return match (true) {
            str_contains($t, 'tolak') => 'rk-merah',
            str_contains($t, 'menunggu') || str_contains($t, 'proses') || str_contains($t, 'belum') => 'rk-oranye',
            default => 'rk-hijau',
        };
    };
@endphp

<div class="rekap-page">

    <!-- Kartu ringkasan -->
    <section class="stats-grid">
        <div class="stat-card">
            <div><div class="stat-number">{{ $ringkasan['hadir'] }}</div><div class="stat-label">Hadir</div></div>
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M20 11.2V12a8 8 0 1 1-4.7-7.3"/><path d="m8.5 11.5 3 3L21 5"/></svg></div>
        </div>
        <div class="stat-card">
            <div><div class="stat-number">{{ $ringkasan['terlambat'] }}</div><div class="stat-label">Terlambat</div></div>
            <div class="stat-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
        </div>
        <div class="stat-card">
            <div><div class="stat-number">{{ $ringkasan['izin'] }}</div><div class="stat-label">Izin / Sakit</div></div>
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h8"/></svg></div>
        </div>
        <div class="stat-card">
            <div><div class="stat-number">{{ $ringkasan['alpa'] }}</div><div class="stat-label">Alpa</div></div>
            <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg></div>
        </div>
    </section>

    <!-- Rekap kehadiran -->
    <div class="content-card">
        <h2 class="card-title">Rekap kehadiran {{ \App\Support\Format::bulan($bulan . '-01') }}</h2>

        <form method="GET" class="rk-filter">
            <select name="bulan" class="custom-select" onchange="this.form.submit()" aria-label="Bulan">
                @foreach ($opsiBulan as $b)
                    <option value="{{ $b }}" @selected($b === $bulan)>{{ \App\Support\Format::bulan($b . '-01') }}</option>
                @endforeach
            </select>

            <select name="status" class="custom-select" onchange="this.form.submit()" aria-label="Status">
                <option value="">Semua Status</option>
                @foreach ($statusPilihan as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </form>

        <div class="rk-table-wrap">
            <table class="rk-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">No</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                        <th>Konfirmasi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($daftar as $a)
                    @php
                        $konfir = $a->konfirmasi;
                        $masalah = $a->kesalahan->contains(fn ($k) => $k->status_perbaikan !== 'Selesai');
                    @endphp
                    <tr>
                        <td>{{ $daftar->firstItem() + $loop->index }}</td>
                        <td class="rk-tanggal">{{ \App\Support\Format::tanggal($a->tanggal_absen) }}</td>
                        <td>{{ $a->jadwal?->shift?->nama_shift ?? '—' }}</td>
                        <td>{{ \App\Support\Format::jam($a->jam_masuk) }}</td>
                        <td>{{ \App\Support\Format::jam($a->jam_pulang) }}</td>
                        <td><span class="rk-badge {{ $kelasStatus($a->status_kehadiran) }}">{{ $a->status_kehadiran }}</span></td>
                        <td>
                            @if ($konfir)
                                <span class="rk-badge {{ $kelasKonfir($konfir->status_konfir) }}">{{ $konfir->status_konfir }}</span>
                            @elseif ($masalah)
                                <a href="{{ route('saya.konfirmasi.form', $a) }}" class="rk-badge rk-oranye rk-link" title="Klik untuk konfirmasi">Belum dikonfirmasi</a>
                            @else
                                <span class="rk-badge rk-hijau">Terkonfirmasi</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="rk-kosong">Belum ada data kehadiran pada bulan ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        <div class="rk-paginasi">
            <span>
                @if ($daftar->total() > 0)
                    Menampilkan {{ $daftar->firstItem() }}–{{ $daftar->lastItem() }} dari {{ $daftar->total() }} data
                @else
                    Tidak ada data
                @endif
            </span>

            @if ($daftar->hasPages())
                <nav class="rk-paginasi-tombol" role="navigation" aria-label="Halaman">
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

<style>
.rekap-page{
    width:100%;
    max-width:none;
    margin:0;
    padding:0 8px 24px;
    box-sizing:border-box;
    color:#111827;
    font-family:'Segoe UI',Inter,Arial,sans-serif
}
.rekap-page *{
    box-sizing:border-box
}
.stat-card,.content-card{
    background:#fff;
    border:1px solid #d0d5dd;
    border-radius:18px
}
.stats-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:16px;
    margin-bottom:20px
}
.stat-card{
    min-height:95px;
    padding:16px 20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    min-width:0
}
.stat-number{
    font-size:clamp(26px,2vw,34px);
    font-weight:600;
    line-height:1.1;
    margin-bottom:7px;
    color:#000
}
.stat-label{
    font-size:13px;
    color:#344054
}
.stat-icon{
    width:46px;
    height:46px;
    flex-shrink:0;
    border-radius:13px;
    background:#eef2f6;
    display:flex;
    align-items:center;
    justify-content:center
}
.stat-icon svg{
    width:23px;
    height:23px;
    stroke:#111827;
    stroke-width:1.8;
    fill:none;
    stroke-linecap:round;
    stroke-linejoin:round
}

.content-card{
    padding:26px 24px 22px
}
.card-title{
    font-size:20px;
    font-weight:500;
    margin:0 0 22px;
    color:#111
}

.rk-filter{
    display:flex;
    align-items:center;
    gap:16px;
    flex-wrap:wrap;
    margin-bottom:20px
}
.custom-select{
    height:44px;
    width:200px;
    padding:0 42px 0 14px;
    border:1px solid #d0dbea;
    border-radius:11px;
    font-size:14px;
    color:#111827;
    background:#fff;
    outline:none;
    cursor:pointer;
    -webkit-appearance:none;
    -moz-appearance:none;
    appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 16px center
}

.rk-table-wrap{
    width:100%;
    overflow-x:auto;
    border:1px solid #acb1b9;
    border-radius:9px
}
.rk-table{
    width:100%;
    border-collapse:collapse;
    min-width:760px;
    white-space:nowrap
}  
.rk-table thead{
    background:#eef2f6
}
.rk-table th{
    height:46px;
    padding:0 15px;
    text-align:left;
    font-size:13px;
    font-weight:500;
    color:#111
}
.rk-table td{
    height:58px;
    padding:0 15px;
    border-top:1px solid #d8dee7;
    font-size:14px;
    color:#111
}
.rk-tanggal{
    font-weight:500
}
.rk-kosong{
    text-align:center;
    color:#667085;
    height:90px !important
}

.rk-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:4px 14px;
    border-radius:14px;
    font-size:12px;
    font-weight:500;
    white-space:nowrap;
    text-decoration:none
}
.rk-hijau{
    color:#16834f;
    background:#e6f4e9
}
.rk-oranye{
    color:#9a4a00;
    background:rgba(255,207,172,.5)
}
.rk-merah{
    color:#c62828;
    background:rgba(234,74,76,.1)
}
.rk-biru{
    color:#1d4ed8;
    background:#dbeafe
}
.rk-abu{
    color:#475467;
    background:#f2f4f7
}
.rk-link{
    cursor:pointer;
    transition:background .2s
}
.rk-link:hover{
    background:rgba(255,207,172,.85)
}

.rk-paginasi{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-top:18px;
    font-size:13px;
    color:#667085;
    flex-wrap:wrap
}
.rk-paginasi-tombol{
    display:flex;
    align-items:center;
    gap:6px
}
.rk-paginasi-tombol a,.rk-paginasi-tombol span{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:34px;
    height:34px;
    padding:0 8px;
    border:1px solid #d0d5dd;
    border-radius:8px;
    background:#fff;
    color:#111827;
    font-size:13px;
    text-decoration:none;
    transition:.2s
}
.rk-paginasi-tombol span.aktif{
    background:#ff941c;
    border-color:#ff941c;
    color:#fff
}
.rk-paginasi-tombol span.nonaktif{
    background:#f8f9fa;
    color:#98a2b3;
    cursor:not-allowed
}
.rk-paginasi-tombol a:hover{
    background:#f8fafc;
    border-color:#98a2b3
}

@media(max-width:1100px){
    .stats-grid{
        grid-template-columns:repeat(2,minmax(0,1fr))
    }
}
@media(max-width:700px){
    .stats-grid{
        grid-template-columns:1fr
    }
    .custom-select{
        width:100%
    }
    .rk-filter>*{
        flex:1 1 100%
    }
}
</style>
@endsection