@extends('layouts.app')

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

<style>
.profil-wrapper {
    width: 100%;
    max-width: none;
    padding: 4px 8px 28px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}
.profil-wrapper * {
    box-sizing: border-box;
}
.profil-grid {
    display: grid;
    grid-template-columns: minmax(270px, 320px) minmax(0, 1fr);
    gap: 22px;
    align-items: stretch;
}
.profil-card {
    background: #ffffff;
    border: 1px solid #d5dbe3;
    border-radius: 18px;
}
.profil-card-left {
    padding: 36px 24px 32px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.profil-avatar-btn {
    position: relative;
    width: 132px;
    height: 132px;
    border-radius: 50%;
    background: #cbd0d8;
    border: none;
    padding: 0;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.profil-avatar-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.12);
}
.profil-avatar-btn img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.profil-avatar-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    color: #ffffff;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.15s ease;
}
.profil-avatar-btn:hover .profil-avatar-overlay {
    opacity: 1;
}
.profil-change-photo {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 14px;
    padding: 4px 12px;
    border: 1px solid #d5dbe3;
    border-radius: 999px;
    background: #f8fafc;
    color: #374151;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.profil-change-photo:hover {
    background: #eef2f6;
    border-color: #9ca3af;
}
.profil-name {
    margin: 0 0 6px;
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
}
.profil-nip {
    margin: 0 0 14px;
    font-size: 13.5px;
    color: #6b7280;
    font-weight: 500;
}
.profil-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 72px;
    height: 28px;
    padding: 0 16px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
}
.profil-badge-aktif {
    background: #e6f4ea;
    color: #1e8e3e;
}
.profil-badge-nonaktif {
    background: #fde8e8;
    color: #c81e1e;
}
.profil-card-right {
    padding: 28px 32px 32px;
}
.profil-right-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 26px;
}
.profil-right-title {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
}
.btn-ubah-password {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    height: 44px;
    padding: 0 20px;
    border: 1px solid #d5dbe3;
    border-radius: 12px;
    background: #ffffff;
    color: #111827;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.btn-ubah-password:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}
.profil-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 40px;
    row-gap: 24px;
}
.profil-field-label {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 5px;
    font-weight: 500;
}
.profil-field-value {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    word-break: break-word;
}
@media (max-width: 860px) {
    .profil-grid {
        grid-template-columns: 1fr;
    }
    .profil-info-grid {
        grid-template-columns: 1fr;
        row-gap: 18px;
    }
}
</style>

<div class="profil-wrapper">
    @error('foto')
        <div class="alert alert-galat" style="margin-bottom: 16px;">{{ $message }}</div>
    @enderror

    <div class="profil-grid">
        <!-- Kartu Kiri: Foto Profil, Nama, NIP, Status -->
        <div class="profil-card profil-card-left">
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
                class="profil-avatar-btn"
                onclick="document.getElementById('inputFotoProfil').click()"
                title="Klik untuk memilih atau mengganti foto profil"
            >
                @if ($fotoProfil)
                    <img id="previewFotoProfil" src="{{ $fotoProfil }}" alt="Foto Profil {{ $namaTampil }}">
                @else
                    <img id="previewFotoProfil" src="" alt="" style="display: none;">
                    <svg id="ikonDefaultProfil" width="58" height="58" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path d="M5 20a7 7 0 0 1 14 0"></path>
                    </svg>
                @endif
                <span class="profil-avatar-overlay">Pilih Foto</span>
            </button>

            <button
                type="button"
                class="profil-change-photo"
                onclick="document.getElementById('inputFotoProfil').click()"
            >
                {{ $fotoProfil ? 'Ganti Foto' : 'Pilih Foto' }}
            </button>

            <h2 class="profil-name">{{ $namaTampil }}</h2>
            <p class="profil-nip">{{ $k?->nip ? 'NIP ' . $k->nip : '@' . $pengguna->username }}</p>
            <span class="profil-badge {{ $isAktif ? 'profil-badge-aktif' : 'profil-badge-nonaktif' }}">
                {{ $statusTeks }}
            </span>
        </div>

        <!-- Kartu Kanan: Informasi Pribadi -->
        <div class="profil-card profil-card-right">
            <div class="profil-right-header">
                <h2 class="profil-right-title">Informasi Pribadi</h2>
                <a href="{{ route('profil.password') }}" class="btn-ubah-password">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="11" width="16" height="10" rx="2"></rect>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                    </svg>
                    Ubah Password
                </a>
            </div>

            <div class="profil-info-grid">
                <div>
                    <div class="profil-field-label">NIP</div>
                    <div class="profil-field-value">{{ $nipTampil }}</div>
                </div>
                <div>
                    <div class="profil-field-label">Nama Lengkap</div>
                    <div class="profil-field-value">{{ $namaTampil }}</div>
                </div>
                <div>
                    <div class="profil-field-label">Jabatan</div>
                    <div class="profil-field-value">{{ $k?->jabatan ?? $pengguna->namaPeran() }}</div>
                </div>
                <div>
                    <div class="profil-field-label">Departemen</div>
                    <div class="profil-field-value">{{ $k?->departemen->nama_dept ?? '-' }}</div>
                </div>
                <div>
                    <div class="profil-field-label">No. Telepon</div>
                    <div class="profil-field-value">{{ $k?->no_hp ?? '-' }}</div>
                </div>
                <div>
                    <div class="profil-field-label">Email</div>
                    <div class="profil-field-value">{{ $k?->email ?? '-' }}</div>
                </div>
                <div>
                    <div class="profil-field-label">Jenis Kelamin</div>
                    <div class="profil-field-value">
                        {{ $k?->jenis_kelamin === 'L' ? 'Laki-laki' : ($k?->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                    </div>
                </div>
                <div>
                    <div class="profil-field-label">Status</div>
                    <div>
                        <span class="profil-badge {{ $isAktif ? 'profil-badge-aktif' : 'profil-badge-nonaktif' }}">
                            {{ $statusTeks }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
@endsection