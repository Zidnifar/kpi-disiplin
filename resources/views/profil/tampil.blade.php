@extends('layouts.app')
@section('title', 'Profil')
@section('judul', 'Profil')

@section('isi')
@php
    $k = $pengguna->karyawan;
    $nipTampil = $k?->nip ?? '-';
    $namaTampil = $k?->nama_karyawan ?? $pengguna->namaTampil();
    $statusTeks = $k?->status_karyawan ?? $pengguna->status_akun ?? 'Aktif';
    $isAktif = $k ? strtolower($k->status_karyawan) === 'aktif' : $pengguna->aktif();
    $fotoProfil = $pengguna->foto ?? null;
@endphp

<div class="profil-page">
    @error('foto')
        <div class="alert alert-galat">{{ $message }}</div>
    @enderror

    <div class="profil-layout">
        <!-- Kartu Kiri: Avatar, Nama, NIP, Status -->
        <div class="profil-card profil-sidebar-card">
            <form id="formFotoProfil" method="POST" action="{{ route('profil.foto') }}" enctype="multipart/form-data">
                @csrf
                <input
                    type="file"
                    id="inputFotoProfil"
                    name="foto"
                    accept="image/jpeg,image/png,image/webp"
                    style="display: none;"
                >
            </form>

            <button
                type="button"
                class="profil-avatar-circle"
                onclick="document.getElementById('inputFotoProfil').click()"
                title="Klik untuk memilih atau mengganti foto profil"
            >
                @if ($fotoProfil)
                    <img id="previewFotoProfil" src="{{ $fotoProfil }}" alt="Foto Profil {{ $namaTampil }}">
                @else
                    <img id="previewFotoProfil" src="" alt="" style="display: none;">
                    <span id="ikonDefaultProfil" class="profil-avatar-ikon">
                        <x-icon nama="user" :ukuran="52" />
                    </span>
                @endif
                <span class="profil-avatar-hover">Pilih Foto</span>
            </button>

            <button
                type="button"
                class="profil-btn-foto"
                onclick="document.getElementById('inputFotoProfil').click()"
            >
                {{ $fotoProfil ? 'Ganti Foto' : 'Pilih Foto' }}
            </button>

            <h2 class="profil-nama">{{ $namaTampil }}</h2>
            <p class="profil-nip">{{ $k?->nip ? 'NIP ' . $k->nip : '@' . $pengguna->username }}</p>
            <span class="profil-status-pill {{ $isAktif ? 'aktif' : 'nonaktif' }}">
                {{ $statusTeks }}
            </span>
        </div>

        <!-- Kartu Kanan: Informasi Pribadi -->
        <div class="profil-card profil-detail-card">
            <div class="profil-detail-header">
                <h2>Informasi Pribadi</h2>
                <a href="{{ route('profil.password') }}" class="btn-ubah-sandi">
                    <x-icon nama="lock" :ukuran="18" />
                    <span>Ubah Password</span>
                </a>
            </div>

            <div class="profil-info-grid">
                <div class="profil-info-item">
                    <span class="label">NIP</span>
                    <strong class="value">{{ $nipTampil }}</strong>
                </div>
                <div class="profil-info-item">
                    <span class="label">Nama Lengkap</span>
                    <strong class="value">{{ $namaTampil }}</strong>
                </div>
                <div class="profil-info-item">
                    <span class="label">Jabatan</span>
                    <strong class="value">{{ $k?->jabatan ?? $pengguna->namaPeran() }}</strong>
                </div>
                <div class="profil-info-item">
                    <span class="label">Departemen</span>
                    <strong class="value">{{ $k?->departemen->nama_dept ?? '-' }}</strong>
                </div>
                <div class="profil-info-item">
                    <span class="label">No. Telepon</span>
                    <strong class="value">{{ $k?->no_hp ?? '-' }}</strong>
                </div>
                <div class="profil-info-item">
                    <span class="label">Email</span>
                    <strong class="value">{{ $k?->email ?? '-' }}</strong>
                </div>
                <div class="profil-info-item">
                    <span class="label">Jenis Kelamin</span>
                    <strong class="value">
                        {{ $k?->jenis_kelamin === 'L' ? 'Laki-laki' : ($k?->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                    </strong>
                </div>
                <div class="profil-info-item">
                    <span class="label">Status</span>
                    <div>
                        <span class="profil-status-pill {{ $isAktif ? 'aktif' : 'nonaktif' }}">
                            {{ $statusTeks }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profil-page {
    width: 100%;
    padding: 0 8px 24px;
    color: #101828;
    box-sizing: border-box;
}

.profil-layout {
    display: grid;
    grid-template-columns: minmax(270px, 320px) minmax(0, 1fr);
    gap: 20px;
    align-items: stretch;
}

.profil-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 20px;
}

.profil-sidebar-card {
    padding: 36px 24px 32px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.profil-avatar-circle {
    position: relative;
    width: 128px;
    height: 128px;
    border-radius: 50%;
    background: #cbd0d8;
    border: 0;
    padding: 0;
    margin: 0 auto 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.profil-avatar-circle:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(16, 24, 40, 0.12);
}

.profil-avatar-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.profil-avatar-ikon {
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.profil-avatar-hover {
    position: absolute;
    inset: 0;
    background: rgba(16, 24, 40, 0.45);
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.15s ease;
}

.profil-avatar-circle:hover .profil-avatar-hover {
    opacity: 1;
}

.profil-btn-foto {
    height: 32px;
    padding: 0 14px;
    margin-bottom: 14px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    background: #fff;
    color: #344054;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}

.profil-btn-foto:hover {
    background: #f8fafc;
    border-color: #98a2b3;
}

.profil-nama {
    margin: 0 0 6px;
    font-size: 20px;
    font-weight: 700;
    color: #101828;
}

.profil-nip {
    margin: 0 0 14px;
    font-size: 13px;
    color: #667085;
}

.profil-status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 68px;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.profil-status-pill.aktif {
    background: #e6f4e9;
    color: #16834f;
}

.profil-status-pill.nonaktif {
    background: #fdebed;
    color: #d92d20;
}

.profil-detail-card {
    padding: 26px 30px 30px;
}

.profil-detail-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
}

.profil-detail-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #101828;
}

.btn-ubah-sandi {
    height: 42px;
    padding: 0 18px;
    border: 1px solid #d0d5dd;
    border-radius: 11px;
    background: #fff;
    color: #101828;
    font-size: 13.5px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    transition: background 0.15s ease, border-color 0.15s ease;
}

.btn-ubah-sandi:hover {
    background: #f8fafc;
    border-color: #98a2b3;
}

.profil-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 36px;
    row-gap: 22px;
}

.profil-info-item .label {
    display: block;
    font-size: 13px;
    color: #667085;
    margin-bottom: 5px;
}

.profil-info-item .value {
    display: block;
    font-size: 15px;
    font-weight: 700;
    color: #101828;
    word-break: break-word;
}

@media (max-width: 860px) {
    .profil-layout {
        grid-template-columns: 1fr;
    }
    .profil-info-grid {
        grid-template-columns: 1fr;
        row-gap: 16px;
    }
}
</style>

@push('skrip')
<script>
document.getElementById('inputFotoProfil')?.addEventListener('change', function (e) {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    const preview = document.getElementById('previewFotoProfil');
    const ikon = document.getElementById('ikonDefaultProfil');
    if (preview) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
    if (ikon) {
        ikon.style.display = 'none';
    }

    document.getElementById('formFotoProfil').submit();
});
</script>
@endpush
@endsection