@extends('layouts.app')

@section('title', 'Dashboard General Manager')
@section('judul', 'Dashboard')

@section('isi')
    @include('dashboard._manajemen_lembur', [
        'judulPintasanLembur' => 'Persetujuan Lembur',
        'ikonPintasanLembur' => 'check',
    ])
@endsection