@extends('layouts.app')

@section('judul', 'Laporan KPI')

@section('isi')
    @if (! $pilihan)
        <div class="kartu"><p class="redup">Belum ada periode penilaian, jadi laporan belum bisa dibuat.</p></div>
    @else
        @php $parameter = array_filter(['semester' => $pilihan['nilai'], 'dept' => $unit ? null : $idDept]); @endphp

        <div class="kartu">
            <h2>Filter Laporan</h2>
            <form method="GET" class="filter-bar">
                <select name="semester" onchange="this.form.submit()">
                    @foreach ($semesterPilihan as $s)
                        <option value="{{ $s['nilai'] }}" @selected($s['nilai'] === $pilihan['nilai'])>{{ $s['label'] }}</option>
                    @endforeach
                </select>
                @if ($unit)
                    <input type="text" value="{{ $namaDept }}" disabled>
                @else
                    <select name="dept" onchange="this.form.submit()">
                        <option value="">Semua Departemen</option>
                        @foreach ($departemen as $d)
                            <option value="{{ $d->id_dept }}" @selected($idDept === $d->id_dept)>{{ $d->nama_dept }}</option>
                        @endforeach
                    </select>
                @endif
                <button type="submit" class="tombol tombol-utama tombol-kecil">Tampilkan Laporan</button>
            </form>
        </div>

        <div class="kartu">
            <div class="kartu-kepala">
                <h2>Pratinjau Laporan</h2>
                <div class="aksi-tabel">
                    <a href="{{ route($awalan . '.laporan.cetak', $parameter) }}" target="_blank" class="tombol tombol-sekunder"><x-icon nama="print" :ukuran="18" /> Cetak / Simpan PDF</a>
                    <a href="{{ route($awalan . '.laporan.excel', $parameter) }}" class="tombol tombol-utama"><x-icon nama="download" :ukuran="18" /> Ekspor Excel</a>
                </div>
            </div>
            @include('laporan._tabel')
        </div>
    @endif
@endsection
