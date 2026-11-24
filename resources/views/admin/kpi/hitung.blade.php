@extends('layouts.app')

@section('judul', 'Hitung Nilai KPI')

@section('isi')
    <div class="breadcrumb"><a href="{{ route('admin.kpi.periode.index') }}">Penilaian KPI</a> / <strong>Hitung Nilai KPI · {{ $periode->nama_periode }}</strong></div>

    <div class="kartu">
        <h2>Rumus Perhitungan</h2>
        <div class="rumus">
            <strong>Nilai Triwulan = 100 − Σ (frekuensi pelanggaran × bobot)</strong><br>
            Nilai Semester = (Triwulan 1 + Triwulan 2) ÷ 2 untuk Semester 1, atau (Triwulan 3 + Triwulan 4) ÷ 2 untuk Semester 2.
        </div>
    </div>

    @if ($belumSelesai > 0)
        <div class="alert alert-peringatan">
            Masih ada {{ $belumSelesai }} kesalahan absensi yang belum diperbaiki pada periode ini.
            <a href="{{ route('admin.kesalahan.index', ['bulan' => $periode->tanggal_mulai->format('Y-m')]) }}">Periksa kesalahan absensi</a>
            sebelum menghitung agar nilai akurat.
        </div>
    @endif
    @if ($tervalidasi > 0)
        <div class="alert alert-info">{{ $tervalidasi }} hasil sudah tervalidasi dan tidak akan ditimpa. Kembalikan dulu dari halaman Hasil KPI bila perlu dihitung ulang.</div>
    @endif

    <div class="kartu">
        <div class="kartu-kepala">
            <h2>Pratinjau perhitungan · {{ $periode->nama_periode }}</h2>
            <span class="redup">{{ \App\Support\Format::tanggal($periode->tanggal_mulai) }} – {{ \App\Support\Format::tanggal($periode->tanggal_selesai) }}</span>
        </div>

        <div class="tabel-bungkus">
            <table class="tabel">
                <thead><tr><th>Karyawan</th><th>Pelanggaran (kode × frekuensi)</th><th class="kanan">Total Pengurang</th><th class="kanan">Nilai Triwulan</th><th>Kategori</th></tr></thead>
                <tbody>
                @forelse ($baris as $b)
                    <tr>
                        <td>{{ $b['karyawan']->nama_karyawan }}</td>
                        <td>{{ $b['rincian'] === [] ? '—' : collect($b['rincian'])->map(fn ($r) => $r['kode'] . '×' . $r['frekuensi'])->implode(', ') }}</td>
                        <td class="kanan">− {{ $b['total'] }}</td>
                        <td class="kanan"><strong>{{ \App\Support\Format::nilai($b['nilai']) }}</strong></td>
                        <td>@if ($b['predikat']) <x-lencana :teks="$b['predikat']" /> @else — @endif</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="kosong">Belum ada karyawan aktif.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('admin.kpi.periode.hitung.proses', $periode) }}" data-konfirmasi="Hitung nilai KPI {{ $periode->nama_periode }}? Hasil yang belum tervalidasi akan dihitung ulang.">
            @csrf
            @if ($belumSelesai > 0)
                <label class="checkbox-baris"><input type="checkbox" name="paksa" value="1"> Hitung tetap meski ada kesalahan absensi yang belum diperbaiki</label>
            @endif
            <div class="aksi-form">
                <a href="{{ route('admin.kpi.periode.index') }}" class="tombol tombol-sekunder">Batal</a>
                <button type="submit" class="tombol tombol-utama"><x-icon nama="chart" :ukuran="18" /> Hitung Nilai KPI</button>
            </div>
        </form>
    </div>
@endsection
