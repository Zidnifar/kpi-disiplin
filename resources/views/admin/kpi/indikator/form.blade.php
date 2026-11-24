@extends('layouts.app')

@php $ubah = $indikator !== null; @endphp
@section('judul', $ubah ? 'Ubah Kriteria' : 'Tambah Kriteria')

@section('isi')
    <div class="breadcrumb"><a href="{{ route('admin.kpi.indikator.index') }}">Indikator KPI</a> / <strong>{{ $ubah ? 'Ubah Kriteria' : 'Tambah Kriteria' }}</strong></div>

    <div class="kartu kartu-form">
        <h2>Formulir Kriteria Disiplin</h2>
        <p class="redup">Bobot adalah jumlah poin yang dikurangkan dari 100 poin untuk setiap pelanggaran.</p>

        @if ($sistem)
            <div class="alert alert-info">Kriteria ini dipakai langsung oleh aturan absensi, jadi kode dan statusnya tidak dapat diubah. Nama dan bobot tetap bisa disesuaikan.</div>
        @endif

        <form method="POST" action="{{ $ubah ? route('admin.kpi.indikator.update', $indikator) : route('admin.kpi.indikator.store') }}" novalidate>
            @csrf
            @if ($ubah) @method('PUT') @endif

            <div class="form-grid">
                <div class="bidang">
                    <label for="kode_indikator">Kode</label>
                    <input id="kode_indikator" name="kode_indikator" type="text" maxlength="10" value="{{ old('kode_indikator', $indikator?->kode_indikator) }}" placeholder="Contoh: TMK" style="text-transform: uppercase" @readonly($sistem) required>
                    @error('kode_indikator') <div class="galat">{{ $message }}</div> @enderror
                </div>
                <div class="bidang">
                    <label for="bobot_poin">Bobot (poin pengurang)</label>
                    <input id="bobot_poin" name="bobot_poin" type="number" min="1" max="100" value="{{ old('bobot_poin', $indikator?->bobot_poin) }}" placeholder="Contoh: 1" required>
                    @error('bobot_poin') <div class="galat">{{ $message }}</div> @enderror
                </div>
                <div class="bidang penuh">
                    <label for="nama_indikator">Kriteria</label>
                    <input id="nama_indikator" name="nama_indikator" type="text" maxlength="100" value="{{ old('nama_indikator', $indikator?->nama_indikator) }}" placeholder="Contoh: Terlambat Masuk Kerja Tanpa Keterangan" required>
                    @error('nama_indikator') <div class="galat">{{ $message }}</div> @enderror
                </div>
                <div class="bidang">
                    <label for="status_indikator">Status</label>
                    <select id="status_indikator" name="status_indikator" @disabled($sistem) required>
                        @foreach (['Aktif', 'Nonaktif'] as $s)
                            <option value="{{ $s }}" @selected(old('status_indikator', $indikator?->status_indikator ?? 'Aktif') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                    <span class="bantuan">Kriteria nonaktif tidak ikut dihitung pada perhitungan berikutnya.</span>
                    @error('status_indikator') <div class="galat">{{ $message }}</div> @enderror
                </div>
            </div>

            @if ($ubah)
                <p class="redup">Perubahan bobot berlaku pada perhitungan berikutnya. Hasil yang sudah tervalidasi tidak berubah.</p>
            @endif

            <div class="aksi-form">
                <a href="{{ route('admin.kpi.indikator.index') }}" class="tombol tombol-sekunder">Batal</a>
                <button type="submit" class="tombol tombol-utama"><x-icon nama="check" :ukuran="18" /> {{ $ubah ? 'Simpan Perubahan' : 'Simpan' }}</button>
            </div>
        </form>
    </div>
@endsection