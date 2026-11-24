@extends('layouts.app')

@section('title', 'Dashboard Kepala Produksi')
@section('judul', 'Dashboard')

@section('isi')
    @include('dashboard._manajemen_lembur', [
        'judulPintasanLembur' => 'Periksa Rencana Lembur',
        'ikonPintasanLembur' => 'doc',
    ])
@endsection