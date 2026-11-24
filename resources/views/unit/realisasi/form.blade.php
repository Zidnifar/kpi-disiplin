@extends('layouts.app')

@section('judul', 'Isi Realisasi Lembur')

@section('isi')
    <div class="breadcrumb"><a href="{{ route('unit.realisasi.index') }}">Realisasi Lembur</a> / <strong>{{ $rencana->no_pengajuan }}</strong></div>

    <div class="kartu">
        <h2>Realisasi {{ $rencana->no_pengajuan }}</h2>
        <p class="redup">{{ \App\Support\Format::tanggal($rencana->tanggal_lembur) }} · rencana {{ \App\Support\Format::rentangJam($rencana->jam_mulai, $rencana->jam_selesai) }}. Isi jam yang benar-benar dilaksanakan setiap karyawan.</p>

        <form method="POST" action="{{ route('unit.realisasi.update', $rencana) }}" novalidate>
            @csrf
            @method('PUT')
            <div class="tabel-bungkus">
                <table class="tabel">
                    <thead><tr><th>Karyawan</th><th class="kanan">Jam Rencana</th><th>Jam Mulai</th><th>Jam Selesai</th><th class="kanan">Total</th><th>Keterangan</th></tr></thead>
                    <tbody>
                    @foreach ($rencana->detail as $d)
                        @php
                            $mulai = old("baris.{$d->id_detail_lembur}.jam_mulai_real", \App\Support\Format::jam($d->jam_mulai_real ?? $rencana->jam_mulai));
                            $selesai = old("baris.{$d->id_detail_lembur}.jam_selesai_real", \App\Support\Format::jam($d->jam_selesai_real ?? $rencana->jam_selesai));
                        @endphp
                        <tr data-realisasi-baris>
                            <td>{{ $d->karyawan->nama_karyawan ?? '—' }}</td>
                            <td class="kanan">{{ \App\Support\Format::jumlahJam((float) $d->jam_rencana) }}</td>
                            <td><input type="time" name="baris[{{ $d->id_detail_lembur }}][jam_mulai_real]" value="{{ $mulai }}" data-mulai required></td>
                            <td><input type="time" name="baris[{{ $d->id_detail_lembur }}][jam_selesai_real]" value="{{ $selesai }}" data-selesai required></td>
                            <td class="kanan"><span data-total>{{ \App\Support\Format::jumlahJam(\App\Services\LemburService::hitungJam($mulai . ':00', $selesai . ':00')) }}</span></td>
                            <td><input type="text" name="baris[{{ $d->id_detail_lembur }}][catatan_realisasi]" value="{{ old("baris.{$d->id_detail_lembur}.catatan_realisasi", $d->catatan_realisasi) }}" maxlength="255" placeholder="Opsional"></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @foreach ($errors->get('baris.*') as $pesan) <div class="galat">{{ $pesan[0] }}</div> @endforeach
            @error('baris') <div class="galat">{{ $message }}</div> @enderror

            <div class="aksi-form">
                <a href="{{ route('unit.realisasi.index') }}" class="tombol tombol-sekunder">Batal</a>
                <button type="submit" class="tombol tombol-utama"><x-icon nama="check" :ukuran="18" /> Simpan Realisasi</button>
            </div>
        </form>
    </div>
@endsection
