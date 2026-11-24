@extends('layouts.app')

@section('title', 'Pemeriksaan Rencana Lembur')
@section('judul', 'Pemeriksaan Rencana Lembur')

@section('isi')
@php
    $statistikAtas = collect($statistik ?? [
        [
            'angka' => $ringkasan['menunggu'] ?? 0,
            'label' => 'Menunggu Pemeriksaan',
            'ikon'  => 'clock',
        ],
        [
            'angka' => $ringkasan['diperiksa'] ?? 0,
            'label' => 'Sudah Diperiksa',
            'ikon'  => 'check',
        ],
        [
            'angka' => $ringkasan['dikembalikan'] ?? 0,
            'label' => 'Dikembalikan',
            'ikon'  => 'x',
        ],
    ]);
@endphp

<div class="periksa-lembur-page">
    <div class="pl-stats-grid">
        @foreach ($statistikAtas as $stat)
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
        <h2 class="pl-main-title">Daftar rencana lembur untuk diperiksa</h2>

        @include('lembur._filter', [
            'actionUrl' => route('lembur.periksa.index'),
            'opsiStatus' => [
                'menunggu'     => 'Menunggu pemeriksaan',
                'diperiksa'    => 'Sudah diperiksa',
                'dikembalikan' => 'Dikembalikan',
            ],
        ])

        @include('lembur._tabel', [
            'ruteDetail' => 'lembur.periksa.show',
            'statusAksi' => \App\Models\RencanaLembur::MENUNGGU_PRODUKSI,
            'labelAksi'  => 'Periksa',
        ])
    </div>
</div>

@include('lembur._gaya')
@endsection