@extends('layouts.app')

@section('judul', 'Pemeriksaan Rencana Lembur')

@section('isi')
    <div class="breadcrumb"><a href="{{ route('lembur.periksa.index') }}">Pemeriksaan Lembur</a> / <strong>{{ $rencana->no_pengajuan }}</strong></div>

    @include('lembur._detail')

    @if ($rencana->status_rencana === \App\Models\RencanaLembur::MENUNGGU_PRODUKSI)
        <div class="kartu kartu-form">
            <form method="POST" action="{{ route('lembur.periksa.periksa', $rencana) }}">
                @csrf
                <div class="bidang">
                    <label for="catatan">Catatan Pemeriksaan (wajib bila dikembalikan)</label>
                    <textarea id="catatan" name="catatan" placeholder="Tulis catatan pemeriksaan...">{{ old('catatan') }}</textarea>
                    @error('catatan') <div class="galat">{{ $message }}</div> @enderror
                </div>
                <div class="aksi-form">
                    <a href="{{ route('lembur.periksa.index') }}" class="tombol tombol-sekunder">Kembali</a>
                    <button type="submit" formaction="{{ route('lembur.periksa.kembalikan', $rencana) }}" class="tombol tombol-bahaya">Kembalikan ke Kepala Unit</button>
                    <button type="submit" class="tombol tombol-utama"><x-icon nama="check" :ukuran="18" /> Selesai Diperiksa</button>
                </div>
            </form>
        </div>
    @else
        <div class="aksi-form"><a href="{{ route('lembur.periksa.index') }}" class="tombol tombol-sekunder">Kembali</a></div>
    @endif
@endsection
