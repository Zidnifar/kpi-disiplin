@extends('layouts.app')

@section('judul', 'Konfirmasi Kehadiran')

@section('isi')
@php
    $daftar->appends(request()->query());

    $batas = $batas ?? null;
@endphp

<div class="kf-page">

    @if ($belum > 0)
        <div class="kf-alert">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <span>
                Anda memiliki {{ $belum }} data kehadiran yang belum dikonfirmasi.
                @if ($batas)
                    Mohon konfirmasi sebelum {{ \App\Support\Format::tanggal($batas) }}.
                @else
                    Mohon konfirmasi agar nilai KPI Anda dihitung dengan data yang benar.
                @endif
            </span>
        </div>
    @endif

    <div class="kf-card">
        <h2 class="kf-title">Data kehadiran yang perlu dikonfirmasi</h2>

        <div class="kf-table-wrap">
            <table class="kf-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">No</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>Masalah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($daftar as $a)
                    @php
                        $konfir = $a->konfirmasi;
                        $masalahBuka = $a->kesalahan->contains(fn ($k) => $k->status_perbaikan !== 'Selesai');
                        $ditolak = $konfir && $konfir->status_konfir === 'Ditolak';
                        $boleh = $konfir ? $ditolak : $masalahBuka;

                        if ($ditolak) {
                            [$label, $kelas] = ['Ditolak, konfirmasi ulang', 'kf-merah'];
                        } elseif ($konfir) {
                            [$label, $kelas] = ['Sudah dikonfirmasi', 'kf-hijau'];
                        } elseif ($masalahBuka) {
                            [$label, $kelas] = ['Belum dikonfirmasi', 'kf-oranye'];
                        } else {
                            [$label, $kelas] = ['Sudah diperbaiki admin', 'kf-hijau'];
                        }
                    @endphp
                    <tr>
                        <td>{{ $daftar->firstItem() + $loop->index }}</td>
                        <td class="kf-tanggal">{{ \App\Support\Format::tanggal($a->tanggal_absen) }}</td>
                        <td>{{ $a->jadwal?->shift?->nama_shift ?? '—' }}</td>
                        <td>{{ \App\Support\Format::jam($a->jam_masuk) }}</td>
                        <td>{{ \App\Support\Format::jam($a->jam_pulang) }}</td>
                        <td>{{ $a->kesalahan->pluck('jenis_kesalahan')->implode('; ') ?: '—' }}</td>
                        <td><span class="kf-badge {{ $kelas }}">{{ $label }}</span></td>
                        <td>
                            <a href="{{ route('saya.konfirmasi.form', $a) }}" class="kf-btn {{ $boleh ? 'kf-btn-oranye' : 'kf-btn-putih' }}">{{ $boleh ? 'Konfirmasi' : 'Lihat' }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="kf-kosong">Tidak ada data kehadiran yang perlu dikonfirmasi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        <div class="kf-paginasi">
            <span>
                @if ($daftar->total() > 0)
                    Menampilkan {{ $daftar->firstItem() }}–{{ $daftar->lastItem() }} dari {{ $daftar->total() }} data
                @else
                    Tidak ada data
                @endif
            </span>

            @if ($daftar->hasPages())
                <nav class="kf-paginasi-tombol" role="navigation" aria-label="Halaman">
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
.kf-page{
    width:100%;
    max-width:none;
    margin:0;
    padding:0 8px 24px;
    box-sizing:border-box;
    color:#111827;
    font-family:'Segoe UI',Inter,Arial,sans-serif
}
.kf-page *{
    box-sizing:border-box;
}

.kf-alert{
    display:flex;
    align-items:center;
    gap:12px;
    padding:16px 20px;
    margin-bottom:22px;
    border:1px solid #f1d5c2;
    border-radius:12px;
    background:#f7e6dc;
    color:#5f2a0c;
    font-size:14px
}
.kf-alert svg{
    width:20px;
    height:20px;
    flex-shrink:0;
    stroke:#111827;
    stroke-width:1.8;
    fill:none;
    stroke-linecap:round;
    stroke-linejoin:round
}

.kf-card{
    background:#fff;
    border:1px solid #d0d5dd;
    border-radius:22px;
    padding:26px 26px 22px
}
.kf-title{
    font-size:20px;
    font-weight:500;
    margin:0 0 22px;
    color:#111
}

.kf-table-wrap{
    width:100%;
    overflow-x:auto;
    border:1px solid #acb1b9;
    border-radius:10px
}
.kf-table{
    width:100%;
    border-collapse:collapse;
    min-width:900px;
    white-space:nowrap
}
.kf-table thead{
    background:#eef2f6
}
.kf-table th{
    height:48px;
    padding:0 18px;
    text-align:left;
    font-size:13px;
    font-weight:500;
    color:#111
}
.kf-table td{
    height:64px;
    padding:0 18px;
    border-top:1px solid #d8dee7;
    font-size:14px;
    color:#111
}
.kf-tanggal{
    font-weight:500
}
.kf-kosong{
    text-align:center;
    color:#667085;
    height:90px !important
}

.kf-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:5px 14px;
    border-radius:14px;
    font-size:12px;
    font-weight:500;
    white-space:nowrap
}
.kf-hijau{
    color:#16834f;
    background:#e6f4e9
}
.kf-oranye{
    color:#9a4a00;
    background:rgba(255,207,172,.5)
}
.kf-merah{
    color:#c62828;
    background:rgba(234,74,76,.1)
}

.kf-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    height:34px;
    padding:0 16px;
    border-radius:9px;
    font-size:13px;
    font-weight:500;
    text-decoration:none;
    border:1px solid transparent;
    transition:background .2s,border-color .2s
}
.kf-btn-oranye{
    background:#ff941c;
    color:#fff
}
.kf-btn-oranye:hover{
    background:#e8830f
}
.kf-btn-putih{
    background:#fff;
    color:#111827;
    border-color:#d0d5dd
}
.kf-btn-putih:hover{
    background:#f8fafc;
    border-color:#98a2b3
}

.kf-paginasi{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-top:20px;
    font-size:13px;
    color:#667085;
    flex-wrap:wrap
}
.kf-paginasi-tombol{
    display:flex;
    align-items:center;
    gap:6px
}
.kf-paginasi-tombol a,.kf-paginasi-tombol span{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:38px;
    height:38px;
    padding:0 8px;
    border:1px solid #d0d5dd;
    border-radius:9px;
    background:#fff;
    color:#111827;
    font-size:13px;
    text-decoration:none;
    transition:.2s
}
.kf-paginasi-tombol span.aktif{
    background:#ff941c;
    border-color:#ff941c;
    color:#fff
}
.kf-paginasi-tombol span.nonaktif{
    background:#f8f9fa;
    color:#98a2b3;
    cursor:not-allowed
}
.kf-paginasi-tombol a:hover{
    background:#f8fafc;
    border-color:#98a2b3
}
</style>
@endsection