@extends('layouts.app')

@section('judul', 'Realisasi Lembur')

@section('isi')
    <div class="kartu-ringkas">
        @include('partials.stat', ['angka' => $ringkasan['belum'], 'label' => 'Belum Diisi', 'ikon' => 'clock'])
        @include('partials.stat', ['angka' => $ringkasan['sudah'], 'label' => 'Sudah Diisi', 'ikon' => 'checkc'])
        @include('partials.stat', ['angka' => \App\Support\Format::jumlahJam((float) $ringkasan['realisasi']), 'label' => 'Total Realisasi', 'ikon' => 'chart'])
        @include('partials.stat', ['angka' => \App\Support\Format::selisihJam((float) $ringkasan['selisih']), 'label' => 'Selisih dari Rencana', 'ikon' => 'sliders'])
    </div>

    <div class="kartu">
        <h2>Realisasi lembur yang sudah disetujui</h2>
        <form method="GET" class="filter-bar">
            <select name="status" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="Disetujui" @selected(request('status') === 'Disetujui')>Belum diisi</option>
                <option value="Selesai" @selected(request('status') === 'Selesai')>Sudah diisi</option>
            </select>
        </form>
        <div class="tabel-bungkus">
            <table class="tabel">
                <thead><tr><th>No. Pengajuan</th><th>Tanggal</th><th class="kanan">Jam Rencana</th><th class="kanan">Jam Realisasi</th><th class="kanan">Selisih</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse ($daftar as $r)
                    @php $real = $r->totalJamRealisasi(); @endphp
                    <tr>
                        <td><strong>{{ $r->no_pengajuan }}</strong></td>
                        <td>{{ \App\Support\Format::tanggal($r->tanggal_lembur) }}</td>
                        <td class="kanan">{{ \App\Support\Format::jumlahJam($r->totalJamRencana()) }}</td>
                        <td class="kanan">{{ $real !== null ? \App\Support\Format::jumlahJam($real) : '—' }}</td>
                        <td class="kanan">{{ $real !== null ? \App\Support\Format::selisihJam($real - $r->totalJamRencana()) : '—' }}</td>
                        <td><x-lencana :teks="$r->status_rencana === 'Selesai' ? 'Sudah diisi' : 'Belum diisi'" /></td>
                        <td><a class="tombol {{ $real === null ? 'tombol-utama' : 'tombol-sekunder' }} tombol-kecil" href="{{ route('unit.realisasi.edit', $r) }}">{{ $real === null ? 'Isi Realisasi' : 'Ubah' }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="kosong">Belum ada rencana lembur yang disetujui.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
