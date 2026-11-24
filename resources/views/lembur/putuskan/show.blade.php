@extends('layouts.app')

@section('title', 'Persetujuan Lembur')
@section('judul', 'Persetujuan Lembur')

@section('isi')
@php
    $riwayatPersetujuan = method_exists($rencana, 'persetujuan') ? $rencana->persetujuan : collect();

    $logPeriksa = $riwayatPersetujuan->firstWhere('tahap', 'periksa');
    $logKetahui = $riwayatPersetujuan->firstWhere('tahap', 'ketahui');
    $logKeputusan = $riwayatPersetujuan->firstWhere('tahap', 'putuskan')
        ?? $riwayatPersetujuan->firstWhere('tahap', 'keputusan');

    $namaPengajuSingkat = $rencana->pengaju->nama_lengkap
        ?? $rencana->pengaju->nama
        ?? $rencana->pengaju->karyawan->nama_lengkap
        ?? 'Pengaju';

    $tglAjukan = $rencana->created_at
        ? \App\Support\Format::tanggalPendek($rencana->created_at)
        : \App\Support\Format::tanggalPendek($rencana->tanggal_lembur);

    $namaPemeriksa = $logPeriksa?->pengguna?->karyawan?->nama_lengkap
        ?? $logPeriksa?->pengguna?->nama_lengkap
        ?? $logPeriksa?->pengguna?->nama
        ?? 'Ka. Produksi';

    $tglPeriksa = $logPeriksa?->created_at
        ? \App\Support\Format::tanggalPendek($logPeriksa->created_at)
        : 'Selesai';

    $namaPengetahui = $logKetahui?->pengguna?->karyawan?->nama_lengkap
        ?? $logKetahui?->pengguna?->nama_lengkap
        ?? $logKetahui?->pengguna?->nama
        ?? 'Opr. Manager';

    $tglKetahui = $logKetahui?->created_at
        ? \App\Support\Format::tanggalPendek($logKetahui->created_at)
        : 'Selesai';

    $statusSaatIni = (string) ($rencana->status_rencana ?? '');
    $bisaDiputuskan = $statusSaatIni === \App\Models\RencanaLembur::MENUNGGU_GM;

    $sudahDiputuskan = $logKeputusan !== null
        || in_array($statusSaatIni, [
            \App\Models\RencanaLembur::DISETUJUI,
            \App\Models\RencanaLembur::DITOLAK,
            \App\Models\RencanaLembur::SELESAI,
        ], true);

    $teksLangkah4 = match ($statusSaatIni) {
        \App\Models\RencanaLembur::DISETUJUI => 'Disetujui',
        \App\Models\RencanaLembur::SELESAI   => 'Selesai',
        \App\Models\RencanaLembur::DITOLAK   => 'Ditolak',
        default                               => 'Menunggu GM',
    };

    $pengajuNama = $rencana->pengaju->nama_lengkap
        ?? $rencana->pengaju->nama
        ?? $rencana->pengaju->karyawan->nama_lengkap
        ?? '—';

    $unitNama = $rencana->departemen->nama_dept ?? '—';
    $tanggalTeks = \App\Support\Format::tanggal($rencana->tanggal_lembur);

    $jamMulai = $rencana->jam_mulai ? substr((string) $rencana->jam_mulai, 0, 5) : null;
    $jamSelesai = $rencana->jam_selesai ? substr((string) $rencana->jam_selesai, 0, 5) : null;
    $detailKaryawan = method_exists($rencana, 'detail') ? $rencana->detail : collect();
    if ((! $jamMulai || ! $jamSelesai) && $detailKaryawan->isNotEmpty()) {
        $barisPertama = $detailKaryawan->first();
        $jamMulai = $jamMulai ?: (isset($barisPertama->jam_mulai) ? substr((string) $barisPertama->jam_mulai, 0, 5) : '');
        $jamSelesai = $jamSelesai ?: (isset($barisPertama->jam_selesai) ? substr((string) $barisPertama->jam_selesai, 0, 5) : '');
    }

    $rentangJamTolak = ($jamMulai && $jamSelesai)
        ? "{$tanggalTeks} · {$jamMulai} – {$jamSelesai}"
        : $tanggalTeks;

    $totalJamTolak = method_exists($rencana, 'totalJamRencana')
        ? \App\Support\Format::jumlahJam((float) $rencana->totalJamRencana())
        : \App\Support\Format::jumlahJam((float) ($rencana->total_jam ?? 0));

    $jumlahOrangTolak = $detailKaryawan->count();
    $bukaModeTolakAwal = $errors->has('alasan_penolakan') || $errors->has('saran_perbaikan') || request()->boolean('tolak');

    $urlSetujui = \Illuminate\Support\Facades\Route::has('lembur.putuskan.setujui')
        ? route('lembur.putuskan.setujui', $rencana)
        : (\Illuminate\Support\Facades\Route::has('lembur.putuskan.putuskan')
            ? route('lembur.putuskan.putuskan', $rencana)
            : '#');

    $urlTolak = \Illuminate\Support\Facades\Route::has('lembur.putuskan.tolak')
        ? route('lembur.putuskan.tolak', $rencana)
        : '#';
@endphp

<div class="periksa-lembur-page">
    <nav class="pl-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('lembur.putuskan.index') }}">Persetujuan Lembur</a>
        <span>/</span>
        <strong id="judulBreadcrumbPutuskan">{{ $bukaModeTolakAwal ? 'Tolak' : 'Tinjau' }} · {{ $rencana->no_pengajuan }}</strong>
    </nav>

    <!-- Mode Normal: Detail, Daftar Karyawan, Alur Persetujuan -->
    <div id="panelTinjauPutuskan" style="{{ $bukaModeTolakAwal ? 'display: none;' : '' }}">
        @include('lembur._detail', ['rencana' => $rencana])

        <div class="pl-card pl-detail-card">
            <h2 class="pl-detail-title" style="margin-bottom: 18px;">Alur Persetujuan</h2>

            <div class="pl-stepper">
                <div class="pl-step done">
                    <div class="pl-step-circle">
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div class="pl-step-title">Diajukan</div>
                    <div class="pl-step-sub">{{ $namaPengajuSingkat }} · {{ $tglAjukan }}</div>
                </div>

                <div class="pl-step done">
                    <div class="pl-step-circle">
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div class="pl-step-title">Diperiksa</div>
                    <div class="pl-step-sub">{{ $namaPemeriksa }} · {{ $tglPeriksa }}</div>
                </div>

                <div class="pl-step done">
                    <div class="pl-step-circle">
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div class="pl-step-title">Diketahui</div>
                    <div class="pl-step-sub">{{ $namaPengetahui }} · {{ $tglKetahui }}</div>
                </div>

                <div class="pl-step {{ $sudahDiputuskan ? 'done' : 'current' }}">
                    <div class="pl-step-circle">
                        @if ($sudahDiputuskan)
                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            <span>4</span>
                        @endif
                    </div>
                    <div class="pl-step-title">Keputusan</div>
                    <div class="pl-step-sub">{{ $teksLangkah4 }}</div>
                </div>
            </div>
        </div>

        @if ($bisaDiputuskan)
            <div class="pl-form-actions">
                <a href="{{ route('lembur.putuskan.index') }}" class="pl-btn pl-btn-sekunder">
                    <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Kembali
                </a>

                <button type="button" onclick="bukaFormTolakGm()" class="pl-btn pl-btn-merah">
                    <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Tolak
                </button>

                <form method="POST" action="{{ $urlSetujui }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="pl-btn pl-btn-hijau">
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Setujui
                    </button>
                </form>
            </div>
        @else
            <div class="pl-form-actions">
                <a href="{{ route('lembur.putuskan.index') }}" class="pl-btn pl-btn-sekunder">
                    <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Kembali
                </a>
            </div>
        @endif
    </div>

    <!-- Mode Tolak Rencana Lembur -->
    <div id="panelTolakPutuskan" style="{{ $bukaModeTolakAwal ? '' : 'display: none;' }}">
        <form method="POST" action="{{ $urlTolak }}">
            @csrf

            <div class="pl-card pl-main-card" style="margin-bottom: 18px;">
                <h2 class="pl-main-title" style="margin-bottom: 4px;">Tolak Rencana Lembur</h2>
                <p class="pl-tolak-sub">Berikan alasan penolakan agar Kepala Unit dapat memperbaiki pengajuan.</p>

                <div class="pl-tolak-ringkasan">
                    <div>
                        <div class="pl-meta-label">No. Pengajuan</div>
                        <div class="pl-meta-value">{{ $rencana->no_pengajuan }}</div>
                    </div>
                    <div>
                        <div class="pl-meta-label">Diajukan oleh</div>
                        <div class="pl-meta-value">{{ $pengajuNama }} · {{ $unitNama }}</div>
                    </div>
                    <div>
                        <div class="pl-meta-label">Tanggal &amp; Jam</div>
                        <div class="pl-meta-value">{{ $rentangJamTolak }}</div>
                    </div>
                    <div>
                        <div class="pl-meta-label">Total Lembur</div>
                        <div class="pl-meta-value">{{ $totalJamTolak }} · {{ $jumlahOrangTolak }} karyawan</div>
                    </div>
                </div>

                <div style="margin-bottom: 18px;">
                    <label for="alasan_penolakan" class="pl-field-label">Alasan Penolakan</label>
                    <textarea
                        id="alasan_penolakan"
                        name="alasan_penolakan"
                        class="pl-catatan-textarea"
                        placeholder="Jelaskan alasan penolakan rencana lembur..."
                    >{{ old('alasan_penolakan') }}</textarea>
                    @error('alasan_penolakan')
                        <div class="pl-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="saran_perbaikan" class="pl-field-label">Saran Perbaikan (opsional)</label>
                    <textarea
                        id="saran_perbaikan"
                        name="saran_perbaikan"
                        class="pl-catatan-textarea pl-textarea-kecil"
                        placeholder="Tuliskan saran untuk pengajuan berikutnya..."
                    >{{ old('saran_perbaikan') }}</textarea>
                    @error('saran_perbaikan')
                        <div class="pl-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="pl-form-actions">
                <button type="button" onclick="tutupFormTolakGm()" class="pl-btn pl-btn-sekunder">
                    Batal
                </button>

                <button type="submit" class="pl-btn pl-btn-merah">
                    <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Kirim Penolakan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaFormTolakGm() {
        document.getElementById('panelTinjauPutuskan').style.display = 'none';
        document.getElementById('panelTolakPutuskan').style.display = 'block';
        document.getElementById('judulBreadcrumbPutuskan').textContent = 'Tolak · {{ $rencana->no_pengajuan }}';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function tutupFormTolakGm() {
        document.getElementById('panelTolakPutuskan').style.display = 'none';
        document.getElementById('panelTinjauPutuskan').style.display = 'block';
        document.getElementById('judulBreadcrumbPutuskan').textContent = 'Tinjau · {{ $rencana->no_pengajuan }}';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>

@include('lembur._gaya')
@endsection