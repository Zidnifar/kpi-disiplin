@extends('layouts.app')

@section('title', 'Info Rencana Lembur')
@section('judul', 'Info Rencana Lembur')

@section('isi')
@php
    $statistikKetahui = collect($statistik ?? [
        [
            'angka' => $ringkasan['menunggu'] ?? 0,
            'label' => 'Menunggu Diketahui',
            'ikon'  => 'clock',
        ],
        [
            'angka' => $ringkasan['diketahui'] ?? ($ringkasan['sudah_diketahui'] ?? 0),
            'label' => 'Sudah Diketahui',
            'ikon'  => 'check',
        ],
        [
            'angka' => $ringkasan['total_bulan_ini'] ?? ($ringkasan['total'] ?? 0),
            'label' => 'Total Bulan Ini',
            'ikon'  => 'doc',
        ],
    ]);
@endphp

<div class="periksa-lembur-page">
    <div class="pl-stats-grid">
        @foreach ($statistikKetahui as $stat)
            <div class="pl-card pl-stat-card">
                <div>
                    <div class="pl-stat-num">{{ $stat['angka'] }}</div>
                    <div class="pl-stat-lbl">{{ $stat['label'] }}</div>
                </div>
                <div class="pl-stat-icon">
                    @if (($stat['ikon'] ?? '') === 'check')
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>
                    @elseif (($stat['ikon'] ?? '') === 'doc')
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="13" y2="17"/></svg>
                    @else
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="pl-card pl-main-card">
        <h2 class="pl-main-title">Daftar rencana lembur</h2>

        @include('lembur._filter', [
            'actionUrl' => route('lembur.ketahui.index'),
            'opsiStatus' => [
                'menunggu'  => 'Menunggu diketahui',
                'diketahui' => 'Sudah diketahui',
                'disetujui' => 'Disetujui',
                'ditolak'   => 'Ditolak',
            ],
        ])

        @include('lembur._tabel', [
            'ruteDetail' => 'lembur.ketahui.show',
            'statusAksi' => \App\Models\RencanaLembur::MENUNGGU_OPERASIONAL,
            'labelAksi'  => 'Lihat',
        ])
    </div>
</div>

@include('lembur._gaya')
@endsection