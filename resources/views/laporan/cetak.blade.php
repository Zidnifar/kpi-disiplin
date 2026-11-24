@extends('layouts.cetak')

@section('judul', 'Laporan KPI')

@section('isi')
    @if ($pilihan)
        @include('laporan._tabel')
        <p class="redup no-print" style="margin-top:16px">Dicetak pada {{ $dicetak }}.</p>
    @else
        <p>Belum ada periode penilaian.</p>
    @endif
@endsection