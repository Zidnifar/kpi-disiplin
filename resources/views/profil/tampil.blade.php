@extends('layouts.app')

@section('judul', 'Profil')

@section('isi')
    <div class="baris-dua">
        <div class="kartu kartu-profil">
            <div class="avatar-besar"><x-icon nama="user" :ukuran="48" /></div>
            <h2>{{ $pengguna->namaTampil() }}</h2>
            <p class="redup">{{ $pengguna->karyawan?->nip ? 'NIP ' . $pengguna->karyawan->nip : '@' . $pengguna->username }}</p>
            <span class="lencana {{ $pengguna->aktif() ? 'lencana-hijau' : 'lencana-merah' }}">{{ $pengguna->status_akun }}</span>
        </div>

        <div class="kartu kartu-lebar">
            <div class="kartu-kepala">
                <h2>Informasi Pribadi</h2>
                <a href="{{ route('profil.password') }}" class="tombol tombol-sekunder"><x-icon nama="lock" :ukuran="18" /> Ubah Password</a>
            </div>

            <div class="grid-info dua-kolom">
                <div><span>Username</span><strong>{{ $pengguna->username }}</strong></div>
                <div><span>Peran</span><strong>{{ $pengguna->namaPeran() }}</strong></div>
                @if ($pengguna->karyawan)
                    @php $k = $pengguna->karyawan; @endphp
                    <div><span>NIP</span><strong>{{ $k->nip ?? '-' }}</strong></div>
                    <div><span>Nama Lengkap</span><strong>{{ $k->nama_karyawan }}</strong></div>
                    <div><span>Jabatan</span><strong>{{ $k->jabatan }}</strong></div>
                    <div><span>Departemen</span><strong>{{ $k->departemen->nama_dept ?? '-' }}</strong></div>
                    <div><span>No. Telepon</span><strong>{{ $k->no_hp ?? '-' }}</strong></div>
                    <div><span>Email</span><strong>{{ $k->email ?? '-' }}</strong></div>
                    <div><span>Jenis Kelamin</span><strong>{{ $k->jenis_kelamin === 'L' ? 'Laki-laki' : ($k->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</strong></div>
                    <div><span>Status Karyawan</span><strong>{{ $k->status_karyawan }}</strong></div>
                @endif
            </div>
        </div>
    </div>
@endsection
