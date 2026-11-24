@extends('layouts.app')

@section('judul', 'Konfirmasi Kehadiran')

@section('isi')
@php
    $namaShift = $absensi->jadwal?->shift?->nama_shift;
    $masalah = $absensi->kesalahan->pluck('jenis_kesalahan')->implode('; ');
    $konfir = $absensi->konfirmasi;
    $jawabanLama = old('jawaban');
@endphp

<div class="kk-page">
    <div class="kk-card">
        <h2 class="kk-title">Konfirmasi kehadiran</h2>

        <!-- Identitas data kehadiran -->
        <div class="kk-ident">
            <div class="kk-nama">{{ $absensi->karyawan->nama_karyawan ?? auth()->user()->namaTampil() }}</div>
            <div class="kk-sub">{{ \App\Support\Format::tanggal($absensi->tanggal_absen) }}{{ $namaShift ? ', Shift ' . strtolower($namaShift) : '' }}</div>
        </div>

        <!-- Data yang tercatat -->
        <div class="kk-info">
            <div><span>Jam masuk tercatat</span>{{ $absensi->jam_masuk ? \App\Support\Format::jam($absensi->jam_masuk) : '— (belum tercatat)' }}</div>
            <div><span>Jam pulang tercatat</span>{{ $absensi->jam_pulang ? \App\Support\Format::jam($absensi->jam_pulang) : '— (belum tercatat)' }}</div>
            <div><span>Masalah</span>{{ $masalah ?: '—' }}</div>
        </div>

        @if ($konfir)
            <div class="kk-alert {{ $konfir->status_konfir === 'Ditolak' ? 'kk-alert-merah' : 'kk-alert-biru' }}">
                Konfirmasi Anda: {{ $konfir->ringkasanJawaban() }} — {{ $konfir->status_konfir }}.
                @if ($konfir->catatan_verifikasi) <br>Catatan admin: {{ $konfir->catatan_verifikasi }} @endif
            </div>
        @endif

        @if ($bolehKirim)
            <form method="POST" action="{{ route('saya.konfirmasi.kirim', $absensi) }}" novalidate>
                @csrf

                <div class="kk-grid">
                    <!-- Kiri: keterangan -->
                    <div>
                        <label class="kk-label" for="alasan">Keterangan</label>
                        <textarea id="alasan" name="alasan" class="kk-textarea" placeholder="Tulis keterangan (wajib diisi bila ada koreksi)">{{ old('alasan') }}</textarea>
                        @error('alasan') <div class="kk-galat">{{ $message }}</div> @enderror
                    </div>

                    <!-- Kanan: jawaban + koreksi -->
                    <div>
                        <label class="kk-label" for="jawaban">Apakah data kehadiran sudah benar?</label>
                        <select id="jawaban" name="jawaban" class="kk-select">
                            <option value="" disabled {{ $jawabanLama ? '' : 'selected' }}>Pilih jawaban</option>
                            <option value="Benar" @selected($jawabanLama === 'Benar')>Ya, data sudah benar</option>
                            <option value="Koreksi" @selected($jawabanLama === 'Koreksi')>Tidak, perlu koreksi</option>
                        </select>
                        @error('jawaban') <div class="kk-galat">{{ $message }}</div> @enderror

                        <div id="bagianKoreksi" class="kk-koreksi">
                            <div>
                                <label class="kk-label" for="jam_masuk_koreksi">Jam masuk sebenarnya</label>
                                <input id="jam_masuk_koreksi" name="jam_masuk_koreksi" type="time" class="kk-input" value="{{ old('jam_masuk_koreksi', $absensi->jam_masuk ? \App\Support\Format::jam($absensi->jam_masuk) : '') }}">
                                @error('jam_masuk_koreksi') <div class="kk-galat">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="kk-label" for="jam_pulang_koreksi">Jam pulang sebenarnya</label>
                                <input id="jam_pulang_koreksi" name="jam_pulang_koreksi" type="time" class="kk-input" value="{{ old('jam_pulang_koreksi', $absensi->jam_pulang ? \App\Support\Format::jam($absensi->jam_pulang) : '') }}">
                                @error('jam_pulang_koreksi') <div class="kk-galat">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="kk-aksi">
                    <button type="submit" class="kk-btn kk-btn-oranye">Kirim</button>
                    <a href="{{ route('saya.konfirmasi') }}" class="kk-btn kk-btn-putih">Batal</a>
                </div>
            </form>
        @else
            <div class="kk-aksi">
                <a href="{{ route('saya.konfirmasi') }}" class="kk-btn kk-btn-putih">Kembali</a>
            </div>
        @endif
    </div>
</div>

<script>
    // Jam masuk/pulang sebenarnya hanya muncul bila jawabannya "perlu koreksi".
    (function () {
        var pilih = document.getElementById('jawaban');
        var bagian = document.getElementById('bagianKoreksi');
        if (!pilih || !bagian) return;
        function atur() { bagian.style.display = pilih.value === 'Koreksi' ? 'grid' : 'none'; }
        pilih.addEventListener('change', atur);
        atur();
    })();
</script>

<style>
.kk-page{
    width:100%;
    max-width:none;
    margin:0;
    padding:0 8px 24px;
    box-sizing:border-box;
    color:#111827;
    font-family:'Segoe UI',Inter,Arial,sans-serif
}   
.kk-page *{
    box-sizing:border-box;
}
.kk-card{
    background:#fff;
    border:1px solid #d0d5dd;
    border-radius:22px;
    padding:26px 28px 24px; 
}
.kk-title{
    font-size:19px;
    font-weight:500;
    margin:0 0 22px;
    color:#111
}
.kk-ident{
    margin-bottom:16px;
}
.kk-nama{
    font-size:15px;
    font-weight:500;
    color:#111
}
.kk-sub{
    font-size:13px;
    color:#667085;
    margin-top:3px
}
.kk-info{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:12px 24px;
    padding:14px 18px;
    margin-bottom:22px;
    border:1px solid #e4e7ec;
    border-radius:12px;
    font-size:14px;
    color:#111
}
.kk-info span{
    display:block;
    font-size:12px;
    color:#667085;
    margin-bottom:3px
}
.kk-alert{
    padding:12px 16px;
    margin-bottom:20px;
    border-radius:12px;
    font-size:13px
}
.kk-alert-biru{
    background:#e8f0fe;
    color:#1d4ed8
}
.kk-alert-merah{
    background:rgba(234,74,76,.1);
    color:#c62828
}

.kk-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:20px 32px;
    align-items:start
}
.kk-label{
    display:block;
    font-size:14px;
    font-weight:500;
    color:#667085;
    margin-bottom:10px
}

.kk-textarea{
    width:100%;
    height:150px;
    padding:12px 14px;
    border:1px solid #d0dbea;
    border-radius:11px;
    font-size:14px;
    font-family:inherit;
    color:#111827;
    resize:vertical;
    outline:none;
    background:#fff
}
.kk-textarea::placeholder,.kk-input::placeholder{color:#98a2b3}
.kk-textarea:focus,.kk-input:focus,.kk-select:focus{border-color:#ff941c}

.kk-select,.kk-input{
    width:100%;
    height:44px;
    padding:0 14px;
    border:1px solid #d0dbea;
    border-radius:11px;
    font-size:14px;
    font-family:inherit;
    color:#111827;
    background:#fff;
    outline:none
}
.kk-select{
    padding-right:42px;
    cursor:pointer;
    -webkit-appearance:none;
    -moz-appearance:none;
    appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 16px center
}

.kk-koreksi{
    display:none;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:16px;
    margin-top:20px
}
.kk-koreksi .kk-label{
    font-size:13px;
    margin-bottom:8px
}

.kk-galat{
    font-size:12px;
    color:#d92d20;
    margin-top:5px
}

.kk-aksi{
    display:flex;
    justify-content:flex-end;
    gap:12px;
    margin-top:26px
}
.kk-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    height:44px;
    padding:0 30px;
    border-radius:11px;
    font-size:14px;
    font-weight:500;
    font-family:inherit;
    text-decoration:none;
    cursor:pointer;
    border:1px solid transparent;
    transition:background .2s,border-color .2s
}
.kk-btn-oranye{
    background:#ff941c;
    color:#fff
}
.kk-btn-oranye:hover{
    background:#e58316
}
.kk-btn-putih{
    background:#fff;
    color:#344054;
    border-color:#d0d5dd;
    padding:0 26px
}
.kk-btn-putih:hover{
    background:#f8fafc;
    border-color:#98a2b3
}

@media(max-width:800px){.kk-grid{grid-template-columns:1fr}.kk-info{grid-template-columns:1fr}.kk-koreksi{grid-template-columns:1fr}}
</style>
@endsection