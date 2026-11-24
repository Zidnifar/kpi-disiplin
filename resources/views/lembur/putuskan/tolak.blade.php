@extends('layouts.app')

@section('judul', 'Tolak Pengajuan Lembur')

@section('isi')
    <div class="breadcrumb"><a href="{{ route('lembur.putuskan.index') }}">Persetujuan Lembur</a> / <a href="{{ route('lembur.putuskan.show', $rencana) }}">{{ $rencana->no_pengajuan }}</a> / <strong>Tolak</strong></div>

    <div class="kartu kartu-form">
        <h2>Tolak Pengajuan {{ $rencana->no_pengajuan }}</h2>
        <p class="redup">{{ $rencana->departemen->nama_dept ?? '' }} · {{ \App\Support\Format::tanggal($rencana->tanggal_lembur) }} · {{ $rencana->detail->count() }} karyawan · {{ \App\Support\Format::jumlahJam($rencana->totalJamRencana()) }}</p>

        <form method="POST" action="{{ route('lembur.putuskan.tolak.kirim', $rencana) }}" data-konfirmasi="Tolak pengajuan ini? Keputusan tidak dapat dibatalkan.">
            @csrf
            <div class="bidang">
                <label for="alasan_penolakan">Alasan Penolakan</label>
                <textarea id="alasan_penolakan" name="alasan_penolakan" placeholder="Tuliskan alasan penolakan..." required>{{ old('alasan_penolakan') }}</textarea>
                @error('alasan_penolakan') <div class="galat">{{ $message }}</div> @enderror
            </div>
            <div class="bidang">
                <label for="saran">Saran Perbaikan (opsional)</label>
                <textarea id="saran" name="saran" placeholder="Tuliskan saran agar kepala unit dapat memperbaiki pengajuan...">{{ old('saran') }}</textarea>
                @error('saran') <div class="galat">{{ $message }}</div> @enderror
            </div>
            <div class="aksi-form">
                <a href="{{ route('lembur.putuskan.show', $rencana) }}" class="tombol tombol-sekunder">Batal</a>
                <button type="submit" class="tombol tombol-bahaya">Tolak Pengajuan</button>
            </div>
        </form>
    </div>
@endsection
