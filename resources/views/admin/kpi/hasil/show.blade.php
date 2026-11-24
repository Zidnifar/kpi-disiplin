@extends('layouts.app')

@section('judul', 'Validasi Hasil KPI')

@section('isi')
    <div class="breadcrumb"><a href="{{ route('admin.kpi.hasil.index', ['periode' => $hasil->id_periode]) }}">Hasil KPI</a> / <strong>Validasi · {{ $hasil->karyawan->nama_karyawan }}</strong></div>

    <div class="kartu">
        <div class="grid-info">
            <div><span>NIP</span><strong>{{ $hasil->karyawan->nip ?? '—' }}</strong></div>
            <div><span>Nama</span><strong>{{ $hasil->karyawan->nama_karyawan }}</strong></div>
            <div><span>Departemen</span><strong>{{ $hasil->karyawan->departemen->nama_dept ?? '—' }}</strong></div>
            <div><span>Periode</span><strong>{{ $hasil->periode->nama_periode }}</strong></div>
            <div><span>Nilai Triwulan</span><strong>{{ \App\Support\Format::nilai($hasil->nilai_triwulan) }} @if ($hasil->predikat) <x-lencana :teks="$hasil->predikat->nama_predikat" /> @endif</strong></div>
            <div><span>Perhitungan</span><strong>{{ $hasil->nilai_awal }} − {{ $hasil->total_pengurang }} = {{ \App\Support\Format::nilai($hasil->nilai_triwulan) }}</strong></div>
        </div>
    </div>

    <div class="kartu">
        <div class="kartu-kepala">
            <h2>Rincian Poin Pengurang</h2>
            <x-lencana :teks="$hasil->status_validasi" />
        </div>
        @include('partials.rincian-kpi', ['hasil' => $hasil])

        <div class="grid-info dua-kolom">
            <div><span>Pemeriksaan kepala unit</span><strong>{{ $hasil->status_periksa }}@if ($hasil->pemeriksa) · {{ $hasil->pemeriksa->namaTampil() }}@endif</strong></div>
            <div><span>Wajib diperiksa kepala unit?</span><strong>{{ $perluDiperiksa ? 'Ya' : 'Tidak (kepala unit/manajemen atau departemen tanpa kepala unit)' }}</strong></div>
        </div>
        @if ($hasil->catatan_periksa) <div class="catatan-kotak"><strong>Catatan pemeriksaan:</strong> {{ $hasil->catatan_periksa }}</div> @endif
        @if ($hasil->catatan_validasi) <div class="catatan-kotak"><strong>Catatan validasi:</strong> {{ $hasil->catatan_validasi }}</div> @endif

        @if ($hasil->status_validasi !== 'Tervalidasi')
            @if ($perluDiperiksa && $hasil->status_periksa !== 'Sudah diperiksa')
                <div class="alert alert-peringatan">Hasil ini belum diperiksa kepala unit, jadi belum bisa divalidasi.</div>
            @endif
            <form method="POST" action="{{ route('admin.kpi.hasil.validasi', $hasil) }}">
                @csrf
                <div class="bidang">
                    <label for="catatan_validasi">Catatan (wajib bila dikembalikan)</label>
                    <textarea id="catatan_validasi" name="catatan_validasi" placeholder="Tulis catatan validasi...">{{ old('catatan_validasi') }}</textarea>
                    @error('catatan_validasi') <div class="galat">{{ $message }}</div> @enderror
                </div>
                <div class="aksi-form">
                    <a href="{{ route('admin.kpi.hasil.index', ['periode' => $hasil->id_periode]) }}" class="tombol tombol-sekunder">Kembali</a>
                    <button type="submit" formaction="{{ route('admin.kpi.hasil.kembalikan', $hasil) }}" class="tombol tombol-bahaya">Kembalikan untuk Dihitung Ulang</button>
                    <button type="submit" class="tombol tombol-utama" @disabled($perluDiperiksa && $hasil->status_periksa !== 'Sudah diperiksa')><x-icon nama="checkc" :ukuran="18" /> Validasi Hasil</button>
                </div>
            </form>
        @else
            <div class="grid-info dua-kolom">
                <div><span>Divalidasi oleh</span><strong>{{ $hasil->pemvalidasi?->namaTampil() ?? '—' }}</strong></div>
                <div><span>Tanggal validasi</span><strong>{{ \App\Support\Format::tanggal($hasil->tanggal_validasi) }}</strong></div>
            </div>
            <div class="aksi-form"><a href="{{ route('admin.kpi.hasil.index', ['periode' => $hasil->id_periode]) }}" class="tombol tombol-sekunder">Kembali</a></div>
        @endif
    </div>
@endsection
