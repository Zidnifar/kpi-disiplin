@extends('layouts.app')
@section('title', 'Impor Data Absensi')
@section('judul', 'Data Absensi')

@section('isi')
<div class="absensi-page" style="width: 100%; padding: 0 8px 24px; color: #101828;">
    <!-- Breadcrumb / Navigasi Halaman -->
    <div class="breadcrumb-area" style="margin-bottom: 20px; font-size: 13px; color: #667085;">
        <a href="{{ route('admin.absensi.index') }}" style="color: #667085; text-decoration: none;">Data Absensi</a> 
        <span style="margin: 0 6px;">/</span> 
        <span style="color: #101828; font-weight: 500;">Impor Data</span>
    </div>

    <!-- Card Formulir Utama -->
    <div class="content-card" style="background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; padding: 28px 32px;">
        <div class="form-header" style="margin-bottom: 24px;">
            <h2 style="font-size: 20px; font-weight: 700; color: #101828; margin-bottom: 4px;">Impor Data Absensi</h2>
            <p style="font-size: 13px; color: #667085;">Unggah file rekap absensi dari mesin absen, lalu periksa sebelum disimpan.</p>
        </div>

        @if ($pratinjau === null)
            <form method="POST" action="{{ route('admin.absensi.pratinjau') }}" enctype="multipart/form-data" novalidate autocomplete="off">
                @csrf

                <!-- Baris 1: Periode Absensi & Sumber Data -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Periode Absensi</label>
                        <input id="periode" name="periode" type="month" value="{{ old('periode', now()->format('Y-m')) }}" style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #101828; outline: none; background: #fff;" required>
                        @error('periode') 
                            <span style="font-size: 12px; color: #d92d20; display: block; margin-top: 4px;">{{ $message }}</span> 
                        @enderror
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #344054; margin-bottom: 8px;">Sumber Data</label>
                        <input type="text" value="Mesin absensi (Excel)" disabled style="width: 100%; height: 44px; padding: 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #344054; background: #f8f9fa; outline: none; cursor: not-allowed;">
                    </div>
                </div>

                <!-- Area Unggah File (Dropzone Bergaris Putus-putus) -->
                <div style="margin-bottom: 32px; position: relative;">
                    <div style="border: 2px dashed #d0d5dd; border-radius: 16px; padding: 36px 20px; text-align: center; background: #fafafa; cursor: pointer; transition: background 0.2s;" onclick="document.getElementById('berkas').click()">
                        <div style="width: 48px; height: 48px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; background: #fff; border-radius: 12px; border: 1px solid #d0d5dd; color: #344054;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                        </div>
                        <p style="font-size: 14px; font-weight: 500; color: #101828; margin: 0 0 4px;" id="file-label-text">Tarik file ke sini atau klik untuk memilih file</p>
                        <p style="font-size: 12px; color: #667085; margin: 0;">Format .xlsx atau .csv · maksimal 10 MB</p>
                        <input id="berkas" name="berkas" type="file" accept=".xlsx,.xls,.csv" style="display: none;" onchange="updateFileName(this)" required>
                    </div>
                    @error('berkas') 
                        <span style="font-size: 12px; color: #d92d20; display: block; margin-top: 6px;">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Tombol Aksi Bawah -->
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                    <a href="{{ route('admin.absensi.index') }}" style="height: 44px; padding: 0 24px; border: 1px solid #d0d5dd; border-radius: 11px; background: #fff; color: #344054; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                        Batal
                    </a>
                    <button type="submit" style="height: 44px; padding: 0 24px; border: 0; border-radius: 11px; background: #ff941c; color: #fff; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: background 0.2s;">
                        <span>👁</span> Lihat Pratinjau
                    </button>
                </div>
            </form>
        @else
            <!-- Tampilan Setelah File Diunggah (Pratinjau) -->
            <div style="margin-bottom: 20px; font-size: 14px; color: #344054;">
                Pratinjau data ({{ count($pratinjau['contoh']) }} dari {{ $pratinjau['total'] }} baris)
            </div>

            <!-- Tabel Pratinjau -->
            <div style="width: 100%; overflow-x: auto; border: 1px solid #d0d5dd; border-radius: 14px; margin-bottom: 32px;">
                <table style="width: 100%; border-collapse: collapse; white-space: nowrap;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 1px solid #d0d5dd;">
                            <th style="height: 48px; padding: 0 20px; font-size: 13px; font-weight: 600; color: #344054; text-align: left;">NIP</th>
                            <th style="height: 48px; padding: 0 20px; font-size: 13px; font-weight: 600; color: #344054; text-align: left;">Nama</th>
                            <th style="height: 48px; padding: 0 20px; font-size: 13px; font-weight: 600; color: #344054; text-align: left;">Tanggal</th>
                            <th style="height: 48px; padding: 0 20px; font-size: 13px; font-weight: 600; color: #344054; text-align: left;">Shift</th>
                            <th style="height: 48px; padding: 0 20px; font-size: 13px; font-weight: 600; color: #344054; text-align: left;">Masuk</th>
                            <th style="height: 48px; padding: 0 20px; font-size: 13px; font-weight: 600; color: #344054; text-align: left;">Pulang</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($pratinjau['contoh'] as $b)
                        <tr style="border-bottom: 1px solid #eef2f6;">
                            <td style="height: 60px; padding: 0 20px; font-size: 14px; color: #101828;">{{ $b['nip'] !== '' ? $b['nip'] : '—' }}</td>
                            <td style="height: 60px; padding: 0 20px; font-size: 14px; color: #101828; font-weight: 500;">{{ $b['nama'] !== '' ? $b['nama'] : '—' }}</td>
                            <td style="height: 60px; padding: 0 20px; font-size: 14px; color: #101828;">{{ $b['tanggal'] ?? '-' }}</td>
                            <td style="height: 60px; padding: 0 20px; font-size: 14px; color: #101828;">{{ $b['shift'] !== '' ? $b['shift'] : '—' }}</td>
                            <td style="height: 60px; padding: 0 20px; font-size: 14px; color: #101828;">{{ $b['jam'] ?? $b['masuk'] ?? '-' }}</td>
                            <td style="height: 60px; padding: 0 20px; font-size: 14px; color: #101828;">{{ $b['pulang'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; height: 90px; color: #667085;">Tidak ada baris yang dapat dibaca.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Form Proses Simpan -->
            <form method="POST" action="{{ route('admin.absensi.proses') }}" autocomplete="off">
                @csrf
                <input type="hidden" name="periode" value="{{ $periode }}">
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="nama_file" value="{{ $namaFile }}">
                
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                    <a href="{{ route('admin.absensi.impor') }}" style="height: 44px; padding: 0 24px; border: 1px solid #d0d5dd; border-radius: 11px; background: #fff; color: #344054; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                        Batal
                    </a>
                    @if ($pratinjau['total'] > 0)
                        <button type="submit" style="height: 44px; padding: 0 24px; border: 0; border-radius: 11px; background: #ff941c; color: #fff; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: background 0.2s;">
                            <span>⚡</span> Impor Data
                        </button>
                    @endif
                </div>
            </form>
        @endif
    </div>
</div>

<script>
function updateFileName(input) {
    if (input.files && input.files[0]) {
        let fileName = input.files[0].name;
        document.getElementById('file-label-text').innerHTML = 'File dipilih: <strong>' + fileName + '</strong>';
    }
}
</script>
@endsection