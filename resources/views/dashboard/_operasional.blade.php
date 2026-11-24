@extends('layouts.app')

@section('title', 'Dashboard Operasional Manager')
@section('judul', 'Dashboard')

@section('isi')
    @include('dashboard._manajemen_lembur', [
        'judulPintasanLembur' => 'Info Rencana Lembur',
        'ikonPintasanLembur' => 'clock',
    ])
@endsection