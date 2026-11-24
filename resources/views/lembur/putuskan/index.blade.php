@extends('layouts.app')

@section('title', 'Persetujuan Lembur')
@section('judul', 'Persetujuan Lembur')

@section('isi')
@php
    $statistikPutuskan = collect($statistik ?? [
        [
            'angka' => $ringkasan['menunggu'] ?? 0,
            'label' => 'Menunggu Keputusan',
            'ikon'  => 'clock',
        ],
        [
            'angka' => $ringkasan['disetujui'] ?? 0,
            'label' => 'Disetujui',
            'ikon'  => 'check',
        ],
        [
            'angka' => $ringkasan['ditolak'] ?? 0,
            'label' => 'Ditolak',
            'ikon'  => 'x',
        ],
    ]);
@endphp

<div class="periksa-lembur-page">
    <div class="pl-stats-grid">
        @foreach ($statistikPutuskan as $stat)
            <div class="pl-card pl-stat-card">
                <div>
                    <div class="pl-stat-num">{{ $stat['angka'] }}</div>
                    <div class="pl-stat-lbl">{{ $stat['label'] }}</div>
                </div>
                <div class="pl-stat-icon">
                    @if (($stat['ikon'] ?? '') === 'check')
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>
                    @elseif (($stat['ikon'] ?? '') === 'x')
                        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    @else
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/></svg>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="pl-card pl-main-card">
        <h2 class="pl-main-title">Daftar rencana lembur untuk diputuskan</h2>

        @include('lembur._filter', [
            'actionUrl' => route('lembur.putuskan.index'),
            'opsiStatus' => [
                'menunggu'  => 'Menunggu keputusan',
                'disetujui' => 'Disetujui',
                'ditolak'   => 'Ditolak',
            ],
        ])

        @include('lembur._tabel', [
            'ruteDetail' => 'lembur.putuskan.show',
            'statusAksi' => \App\Models\RencanaLembur::MENUNGGU_GM,
            'labelAksi'  => 'Tinjau',
        ])
    </div>
</div>

@include('lembur._gaya')
@endsection