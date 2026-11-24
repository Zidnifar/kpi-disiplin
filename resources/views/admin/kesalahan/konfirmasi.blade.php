@extends('layouts.app')

@section('judul', 'Konfirmasi Status Kehadiran')

@section('isi')
    <div class="breadcrumb"><a href="{{ route('admin.kesalahan.index', ['bulan' => $absensi->tanggal_absen->format('Y-m')]) }}">Pemeriksaan Kesalahan</a> / <strong>Konfirmasi Status Kehadiran</strong></div>

    <div class="kartu kartu-form">
        <h2>Konfirmasi Status Kehadiran</h2>
        <p class="redup">Tetapkan status kehadiran untuk data yang bermasalah.</p>

        <div class="grid-info dua-kolom">
            <div><span>Karyawan</span><strong>{{ $absensi->karyawan->nama_karyawan ?? '—' }}</strong></div>
            <div><span>Tanggal</span><strong>{{ \App\Support\Format::tanggal($absensi->tanggal_absen) }}</strong></div>
            <div><span>Shift · Masuk · Pulang</span><strong>{{ $absensi->jadwal?->shift?->nama_shift ?? '—' }} · {{ \App\Support\Format::jam($absensi->jam_masuk) }} · {{ \App\Support\Format::jam($absensi->jam_pulang) }}</strong></div>
            <div><span>Status tercatat</span><strong>{{ $absensi->status_kehadiran }}</strong></div>
            <div style="grid-column: 1 / -1"><span>Masalah</span><strong>{{ $absensi->kesalahan->pluck('jenis_kesalahan')->implode('; ') ?: '—' }}</strong></div>
        </div>

        @if ($absensi->konfirmasi)
            <div class="alert alert-info">
                Karyawan sudah mengirim konfirmasi ({{ $absensi->konfirmasi->ringkasanJawaban() }}) — status: {{ $absensi->konfirmasi->status_konfir }}.
                @if ($absensi->konfirmasi->status_konfir === 'Menunggu verifikasi')
                    <a href="{{ route('admin.verifikasi.show', $absensi->konfirmasi) }}">Buka halaman verifikasi</a>.
                @endif
            </div>
        @endif

        @if ($selesai)
            <div class="alert alert-sukses">Kesalahan ini sudah diperbaiki. Status akhir: {{ $absensi->status_kehadiran }}.</div>
            @php $catatan = $absensi->kesalahan->firstWhere('catatan', '!=', null)?->catatan; @endphp
            @if ($catatan) <div class="catatan-kotak">{{ $catatan }}</div> @endif
            <div class="aksi-form"><a href="{{ route('admin.kesalahan.index', ['bulan' => $absensi->tanggal_absen->format('Y-m')]) }}" class="tombol tombol-sekunder">Kembali</a></div>
        @else
            <form method="POST" action="{{ route('admin.kesalahan.simpan', $absensi) }}" novalidate>
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="bidang penuh">
                        <label for="status_kehadiran">Status Kehadiran</label>
                        <select id="status_kehadiran" name="status_kehadiran" required>
                            <option value="">Pilih status</option>
                            @foreach ($statusPilihan as $s)
                                <option value="{{ $s }}" @selected(old('status_kehadiran') === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('status_kehadiran') <div class="galat">{{ $message }}</div> @enderror
                    </div>
                    <div class="bidang">
                        <label for="jam_masuk">Jam Masuk Final</label>
                        <input id="jam_masuk" name="jam_masuk" type="time" value="{{ old('jam_masuk', \App\Support\Format::jam($absensi->jam_masuk) === '—' ? '' : \App\Support\Format::jam($absensi->jam_masuk)) }}">
                        @error('jam_masuk') <div class="galat">{{ $message }}</div> @enderror
                    </div>
                    <div class="bidang">
                        <label for="jam_pulang">Jam Pulang Final</label>
                        <input id="jam_pulang" name="jam_pulang" type="time" value="{{ old('jam_pulang', \App\Support\Format::jam($absensi->jam_pulang) === '—' ? '' : \App\Support\Format::jam($absensi->jam_pulang)) }}">
                        @error('jam_pulang') <div class="galat">{{ $message }}</div> @enderror
                    </div>
                    <div class="bidang penuh">
                        <label for="catatan">Catatan</label>
                        <textarea id="catatan" name="catatan" placeholder="Tambahkan catatan konfirmasi...">{{ old('catatan') }}</textarea>
                        @error('catatan') <div class="galat">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="aksi-form">
                    <a href="{{ route('admin.kesalahan.index', ['bulan' => $absensi->tanggal_absen->format('Y-m')]) }}" class="tombol tombol-sekunder">Batal</a>
                    <button type="submit" class="tombol tombol-utama"><x-icon nama="check" :ukuran="18" /> Simpan Status</button>
                </div>
            </form>
        @endif
    </div>
@endsection
