@extends('layouts.app')

@section('judul', 'Verifikasi Konfirmasi')

@section('isi')
    <div class="breadcrumb"><a href="{{ route('admin.verifikasi.index') }}">Verifikasi Konfirmasi</a> / <strong>Verifikasi · {{ $absensi->karyawan->nama_karyawan ?? '' }} · {{ \App\Support\Format::tanggal($absensi->tanggal_absen) }}</strong></div>

    <div class="dua-kartu">
        <div class="kartu">
            <h2>Data Tercatat Sistem</h2>
            <div class="grid-info dua-kolom">
                <div><span>Tanggal</span><strong>{{ \App\Support\Format::tanggal($absensi->tanggal_absen) }}</strong></div>
                <div><span>Shift</span><strong>{{ $absensi->jadwal?->shift?->nama_shift ?? '—' }}</strong></div>
                <div><span>Jam Masuk</span><strong>{{ \App\Support\Format::jam($absensi->jam_masuk) }}</strong></div>
                <div><span>Jam Pulang</span><strong>{{ $absensi->jam_pulang ? \App\Support\Format::jam($absensi->jam_pulang) : '— (kosong)' }}</strong></div>
                <div><span>Status</span><strong>{{ $absensi->status_kehadiran }}</strong></div>
                <div><span>Catatan sistem</span><strong>{{ $absensi->keterangan_absen ?? '—' }}</strong></div>
            </div>
        </div>

        <div class="kartu">
            <div class="kartu-kepala">
                <h2>Konfirmasi Karyawan</h2>
                <x-lencana :teks="$konfirmasi->status_konfir" />
            </div>
            <div class="grid-info dua-kolom">
                <div><span>Jawaban</span><strong>{{ $konfirmasi->jawaban_konfir === 'Koreksi' ? 'Perlu koreksi' : 'Data sudah benar' }}</strong></div>
                <div><span>Dikirim</span><strong>{{ \App\Support\Format::tanggal($konfirmasi->tanggal_konfir) }}</strong></div>
                @if ($konfirmasi->jawaban_konfir === 'Koreksi')
                    <div><span>Jam Masuk Sebenarnya</span><strong>{{ \App\Support\Format::jam($konfirmasi->jam_masuk_koreksi) }}</strong></div>
                    <div><span>Jam Pulang Sebenarnya</span><strong>{{ \App\Support\Format::jam($konfirmasi->jam_pulang_koreksi) }}</strong></div>
                @endif
                <div style="grid-column: 1 / -1"><span>Alasan</span><strong>{{ $konfirmasi->alasan ?? '—' }}</strong></div>
            </div>
        </div>
    </div>

    <div class="kartu kartu-form">
        @if ($konfirmasi->status_konfir === 'Menunggu verifikasi')
            <form method="POST" action="{{ route('admin.verifikasi.verifikasi', $konfirmasi) }}" id="form-verifikasi">
                @csrf
                <div class="bidang">
                    <label for="catatan_verifikasi">Catatan Verifikasi (wajib bila menolak)</label>
                    <textarea id="catatan_verifikasi" name="catatan_verifikasi" placeholder="Tulis catatan verifikasi...">{{ old('catatan_verifikasi') }}</textarea>
                    @error('catatan_verifikasi') <div class="galat">{{ $message }}</div> @enderror
                </div>
                <div class="aksi-form">
                    <a href="{{ route('admin.verifikasi.index') }}" class="tombol tombol-sekunder">Kembali</a>
                    <button type="submit" formaction="{{ route('admin.verifikasi.tolak', $konfirmasi) }}" class="tombol tombol-bahaya">Tolak</button>
                    <button type="submit" class="tombol tombol-utama"><x-icon nama="check" :ukuran="18" /> Verifikasi</button>
                </div>
            </form>
        @else
            <div class="grid-info dua-kolom">
                <div><span>Diproses oleh</span><strong>{{ $konfirmasi->pemverifikasi?->namaTampil() ?? '—' }}</strong></div>
                <div><span>Tanggal proses</span><strong>{{ \App\Support\Format::tanggal($konfirmasi->tanggal_verifikasi) }}</strong></div>
            </div>
            @if ($konfirmasi->catatan_verifikasi) <div class="catatan-kotak">{{ $konfirmasi->catatan_verifikasi }}</div> @endif
            <div class="aksi-form"><a href="{{ route('admin.verifikasi.index') }}" class="tombol tombol-sekunder">Kembali</a></div>
        @endif
    </div>
@endsection
