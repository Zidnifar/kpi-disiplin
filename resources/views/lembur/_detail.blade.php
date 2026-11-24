@php
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

    $rentangJam = ($jamMulai && $jamSelesai)
        ? "{$tanggalTeks} · {$jamMulai} – {$jamSelesai}"
        : $tanggalTeks;

    $jumlahOrang = $detailKaryawan->count();
    $totalJamRencana = method_exists($rencana, 'totalJamRencana')
        ? \App\Support\Format::jumlahJam((float) $rencana->totalJamRencana())
        : \App\Support\Format::jumlahJam((float) ($rencana->total_jam ?? 0));

    $statusRaw = (string) ($rencana->status_rencana ?? '');
    [$teksBadgeDetail, $kelasBadgeDetail] = match ($statusRaw) {
        \App\Models\RencanaLembur::MENUNGGU_PRODUKSI => ['Menunggu pemeriksaan', 'badge-menunggu'],
        \App\Models\RencanaLembur::DIKEMBALIKAN      => ['Dikembalikan', 'badge-dikembalikan'],
        default                                        => ['Sudah diperiksa', 'badge-selesai'],
    };
@endphp

<div class="pl-card pl-detail-card">
    <div class="pl-detail-head">
        <h2 class="pl-detail-title">Informasi Pengajuan</h2>
        <span class="pl-badge {{ $kelasBadgeDetail }}">{{ $teksBadgeDetail }}</span>
    </div>

    <div class="pl-meta-grid">
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
            <div class="pl-meta-value">{{ $rentangJam }}</div>
        </div>
        <div>
            <div class="pl-meta-label">Total Lembur</div>
            <div class="pl-meta-value">{{ $totalJamRencana }} · {{ $jumlahOrang }} karyawan</div>
        </div>
    </div>
</div>

<div class="pl-card pl-detail-card">
    <h2 class="pl-detail-title" style="margin-bottom: 16px;">Daftar karyawan lembur</h2>

    <div class="pl-table-wrap" style="margin-bottom: 0;">
        <table class="pl-table">
            <thead>
                <tr>
                    <th class="pl-col-no">No</th>
                    <th>Karyawan</th>
                    <th>Jabatan</th>
                    <th>Jam Lembur</th>
                    <th>Pekerjaan</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($detailKaryawan as $i => $d)
                @php
                    $namaKaryawan = $d->karyawan->nama_lengkap
                        ?? $d->karyawan->nama
                        ?? $d->nama_karyawan
                        ?? '—';

                    $jabatanKaryawan = $d->karyawan->jabatan->nama_jabatan
                        ?? $d->karyawan->jabatan
                        ?? $d->jabatan
                        ?? '—';

                    $mulaiBaris = isset($d->jam_mulai) ? substr((string) $d->jam_mulai, 0, 5) : $jamMulai;
                    $selesaiBaris = isset($d->jam_selesai) ? substr((string) $d->jam_selesai, 0, 5) : $jamSelesai;
                    $jamBaris = ($mulaiBaris && $selesaiBaris) ? "{$mulaiBaris} – {$selesaiBaris}" : '—';

                    $pekerjaanBaris = $d->pekerjaan ?? $d->uraian_pekerjaan ?? $d->keterangan ?? $rencana->keperluan ?? '—';
                @endphp
                <tr>
                    <td class="pl-col-no">{{ $i + 1 }}</td>
                    <td><strong>{{ $namaKaryawan }}</strong></td>
                    <td>{{ $jabatanKaryawan }}</td>
                    <td>{{ $jamBaris }}</td>
                    <td>{{ $pekerjaanBaris }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="pl-empty">Belum ada rincian karyawan lembur.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>