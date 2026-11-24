@extends('layouts.app')

@section('judul', 'Rekap KPI')

@section('isi')
    <div class="kartu">
        <div class="kartu-kepala">
            <h2>Rekap KPI</h2>
            <div class="aksi-tabel">
                <a href="{{ route('admin.laporan') }}" class="tombol tombol-sekunder"><x-icon nama="file" :ukuran="18" /> Laporan</a>
                <a href="{{ route('admin.kpi.rekap.excel', ['tahun' => $tahun, 'dept' => $idDept]) }}" class="tombol tombol-sekunder"><x-icon nama="download" :ukuran="18" /> Ekspor</a>
            </div>
        </div>
        <form method="GET" class="filter-bar">
            <select name="tahun" onchange="this.form.submit()">
                @forelse ($tahunPilihan as $t)
                    <option value="{{ $t }}" @selected($t === $tahun)>{{ $t }}</option>
                @empty
                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                @endforelse
            </select>
            <select name="tw" onchange="this.form.submit()">
                @foreach ([1, 2, 3, 4] as $t)
                    <option value="{{ $t }}" @selected($t === $tw)>Grafik Triwulan {{ $t }}</option>
                @endforeach
            </select>
            <select name="dept" onchange="this.form.submit()">
                <option value="">Semua Departemen</option>
                @foreach ($departemen as $d)
                    <option value="{{ $d->id_dept }}" @selected($idDept === $d->id_dept)>{{ $d->nama_dept }}</option>
                @endforeach
            </select>
        </form>
        <p class="redup">Hanya nilai yang sudah divalidasi yang ditampilkan.</p>
    </div>

    <div class="kartu">
        <h2>Rata-rata nilai per departemen · Triwulan {{ $tw }} {{ $tahun }}</h2>
        <div class="grafik">
            @forelse ($rataDept as $d)
                <div class="bar-baris">
                    <span>{{ $d['dept'] }}</span>
                    <div class="bar-jalur"><div class="bar-isi" style="width: {{ min(100, max(0, $d['rata'])) }}%"></div></div>
                    <span class="bar-nilai">{{ \App\Support\Format::nilai($d['rata']) }}</span>
                </div>
            @empty
                <p class="redup">Belum ada nilai tervalidasi untuk triwulan ini.</p>
            @endforelse
        </div>
    </div>

    <div class="kartu">
        <h2>Rekap nilai karyawan {{ $tahun }}</h2>
        <div class="tabel-bungkus">
            <table class="tabel">
                <thead><tr><th>Nama</th><th>Departemen</th><th class="kanan">TW 1</th><th class="kanan">TW 2</th><th class="kanan">Semester 1</th><th class="kanan">TW 3</th><th class="kanan">TW 4</th><th class="kanan">Semester 2</th></tr></thead>
                <tbody>
                @forelse ($baris as $r)
                    <tr>
                        <td>{{ $r['karyawan']->nama_karyawan }}</td>
                        <td>{{ $r['karyawan']->departemen->nama_dept ?? '—' }}</td>
                        <td class="kanan">{{ \App\Support\Format::nilai($r['tw'][1]) }}</td>
                        <td class="kanan">{{ \App\Support\Format::nilai($r['tw'][2]) }}</td>
                        <td class="kanan"><strong>{{ \App\Support\Format::nilai($r['smt1']) }}</strong></td>
                        <td class="kanan">{{ \App\Support\Format::nilai($r['tw'][3]) }}</td>
                        <td class="kanan">{{ \App\Support\Format::nilai($r['tw'][4]) }}</td>
                        <td class="kanan"><strong>{{ \App\Support\Format::nilai($r['smt2']) }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="kosong">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
