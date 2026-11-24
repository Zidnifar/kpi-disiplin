@extends('layouts.app')

@section('judul', 'Rencana Lembur')

@section('isi')
@php
    $daftar->appends(request()->query());
    $opsiBulan = collect(range(0, 12))->map(fn ($i) => now()->startOfMonth()->subMonths($i)->format('Y-m'));
    if (request('bulan') && ! $opsiBulan->contains(request('bulan'))) {
        $opsiBulan->push(request('bulan'));
    }
    $opsiBulan = $opsiBulan->sortDesc()->values();

    $kelasStatus = function ($status) {
        $s = strtolower((string) $status);

        return match (true) {
            str_contains($s, 'tolak') => 'rl-merah',
            str_contains($s, 'setuju') || str_contains($s, 'selesai') => 'rl-hijau',
            str_contains($s, 'kembali') => 'rl-kuning',
            str_contains($s, 'produksi') => 'rl-oranye',
            str_contains($s, 'operasional') || str_contains($s, 'general') => 'rl-biru',
            default => 'rl-abu', // Draf
        };
    };
@endphp

<div class="rl-page">

    <!-- Kartu ringkasan -->
    <section class="rl-stats">
        <div class="rl-stat">
            <div><div class="rl-stat-angka">{{ $ringkasan['total'] }}</div><div class="rl-stat-label">Total Pengajuan</div></div>
            <div class="rl-stat-ikon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h8"/></svg></div>
        </div>
        <div class="rl-stat">
            <div><div class="rl-stat-angka">{{ $ringkasan['proses'] }}</div><div class="rl-stat-label">Dalam Proses</div></div>
            <div class="rl-stat-ikon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
        </div>
        <div class="rl-stat">
            <div><div class="rl-stat-angka">{{ $ringkasan['disetujui'] }}</div><div class="rl-stat-label">Disetujui</div></div>
            <div class="rl-stat-ikon"><svg viewBox="0 0 24 24"><path d="M20 11.2V12a8 8 0 1 1-4.7-7.3"/><path d="m8.5 11.5 3 3L21 5"/></svg></div>
        </div>
        <div class="rl-stat">
            <div><div class="rl-stat-angka">{{ $ringkasan['ditolak'] }}</div><div class="rl-stat-label">Ditolak</div></div>
            <div class="rl-stat-ikon"><svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg></div>
        </div>
    </section>

    <!-- Daftar -->
    <div class="rl-card">
        <div class="rl-kepala">
            <h2 class="rl-judul">Daftar rencana kerja lembur</h2>
            <a href="{{ route('unit.lembur.create') }}" class="rl-btn-utama">
                <span class="rl-plus">+</span> Ajukan Rencana Lembur
            </a>
        </div>

        <form method="GET" class="rl-filter">
            <div class="rl-cari">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari no. pengajuan...">
            </div>

            <select name="bulan" class="rl-select" onchange="this.form.submit()" aria-label="Bulan">
                <option value="">Semua Bulan</option>
                @foreach ($opsiBulan as $b)
                    <option value="{{ $b }}" @selected(request('bulan') === $b)>{{ \App\Support\Format::bulan($b . '-01') }}</option>
                @endforeach
            </select>

            <select name="status" class="rl-select" onchange="this.form.submit()" aria-label="Status">
                <option value="">Semua Status</option>
                @foreach (['Draf', 'Menunggu Ka. Produksi', 'Menunggu Operasional Manager', 'Menunggu General Manager', 'Dikembalikan', 'Disetujui', 'Ditolak', 'Selesai'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </form>

        <div class="rl-tabel-bungkus">
            <table class="rl-tabel">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>No. Pengajuan</th>
                        <th>Tanggal Lembur</th>
                        <th>Jam Lembur</th>
                        <th>Karyawan</th>
                        <th>Total Jam</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($daftar as $r)
                    @php
                        $statusSekarang = strtolower((string) $r->status_rencana);
                        $langkah = str_contains($statusSekarang, 'kembali')
                            ? 0
                            : \App\Services\DashboardKaryawanService::langkahLembur($r->status_rencana);

                        $payloadDetail = [
                            'no_pengajuan' => $r->no_pengajuan,
                            'departemen' => $r->departemen->nama_dept ?? ($departemen->nama_dept ?? '—'),
                            'tanggal_lembur' => \App\Support\Format::tanggal($r->tanggal_lembur),
                            'jam_lembur' => \App\Support\Format::rentangJam($r->jam_mulai, $r->jam_selesai),
                            'shift' => $r->shift->nama_shift ?? '—',
                            'total_jam' => \App\Support\Format::jumlahJam($r->totalJamRencana()),
                            'status' => $r->status_rencana,
                            'status_kelas' => $kelasStatus($r->status_rencana),
                            'langkah' => $langkah,
                            'ditolak' => str_contains($statusSekarang, 'tolak'),
                            'uraian_pekerjaan' => $r->uraian_pekerjaan ?: '—',
                            'alasan_lembur' => $r->alasan_lembur ?: '—',
                            'catatan_tolak' => method_exists($r, 'catatanPenolakan') ? $r->catatanPenolakan() : null,
                            'boleh_ubah' => method_exists($r, 'bolehDiubah') ? $r->bolehDiubah() : false,
                            'bisa_realisasi' => $r->status_rencana === \App\Models\RencanaLembur::DISETUJUI,
                            'url_edit' => route('unit.lembur.edit', $r),
                            'url_ajukan' => route('unit.lembur.ajukan', $r),
                            'url_realisasi' => \Illuminate\Support\Facades\Route::has('unit.realisasi.edit')
                                ? route('unit.realisasi.edit', $r)
                                : null,
                            'karyawan' => $r->detail->map(fn ($d) => [
                                'nama' => $d->karyawan->nama_karyawan ?? ($d->nama_karyawan ?? '—'),
                                'jabatan' => $d->karyawan->jabatan ?? '—',
                                'pekerjaan' => $d->pekerjaan ?: '—',
                            ])->values()->all(),
                        ];
                    @endphp
                    <tr>
                        <td>{{ $daftar->firstItem() + $loop->index }}</td>
                        <td class="rl-tebal">{{ $r->no_pengajuan }}</td>
                        <td>{{ \App\Support\Format::tanggal($r->tanggal_lembur) }}</td>
                        <td>{{ \App\Support\Format::rentangJam($r->jam_mulai, $r->jam_selesai) }}</td>
                        <td>{{ $r->detail->count() }}</td>
                        <td>{{ \App\Support\Format::jumlahJam($r->totalJamRencana()) }}</td>
                        <td><span class="rl-badge {{ $kelasStatus($r->status_rencana) }}">{{ $r->status_rencana }}</span></td>
                        <td>
                            <button type="button" class="rl-btn-putih" data-buka-detail-lembur data-detail='@json($payloadDetail)'>Detail</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="rl-kosong">Belum ada pengajuan lembur.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        <div class="rl-paginasi">
            <span>
                @if ($daftar->total() > 0)
                    Menampilkan {{ $daftar->firstItem() }}–{{ $daftar->lastItem() }} dari {{ $daftar->total() }} data
                @else
                    Tidak ada data
                @endif
            </span>

            @if ($daftar->hasPages())
                <nav class="rl-paginasi-tombol" role="navigation" aria-label="Halaman">
                    @if ($daftar->onFirstPage())
                        <span class="nonaktif">‹</span>
                    @else
                        <a href="{{ $daftar->previousPageUrl() }}" rel="prev">‹</a>
                    @endif

                    @foreach ($daftar->linkCollection() as $link)
                        @if ($link['url'] === null)
                            @if ($link['label'] === '...')
                                <span class="nonaktif">...</span>
                            @endif
                        @else
                            @if ($link['active'])
                                <span class="aktif">{{ $link['label'] }}</span>
                            @else
                                <a href="{{ $link['url'] }}">{!! $link['label'] == '&laquo; Previous' ? '‹' : ($link['label'] == 'Next &raquo;' ? '›' : $link['label']) !!}</a>
                            @endif
                        @endif
                    @endforeach

                    @if ($daftar->hasMorePages())
                        <a href="{{ $daftar->nextPageUrl() }}" rel="next">›</a>
                    @else
                        <span class="nonaktif">›</span>
                    @endif
                </nav>
            @endif
        </div>
    </div>
</div>

<div class="lm-backdrop" id="modalDetailLembur" role="dialog" aria-modal="true" aria-labelledby="judulModalLembur" aria-hidden="true">
    <div class="lm-modal">
        <div class="lm-kepala">
            <div>
                <h3 class="lm-judul" id="judulModalLembur">Detail Rencana Lembur</h3>
                <div class="lm-sub"><span id="detailNoPengajuan">—</span></div>
            </div>
            <div class="lm-kepala-kanan">
                <span id="modalLemburStatusBadge"></span>
                <button type="button" class="lm-tutup" id="tutupModalLembur" aria-label="Tutup pop up">&times;</button>
            </div>
        </div>

        <div class="lm-tubuh">
            <div id="modalLemburPesan" class="lm-pesan" style="display: none;"></div>

            <!-- Tahap persetujuan -->
            <div class="lm-stepper" id="detailStepper"></div>

            <!-- Informasi -->
            <div class="lm-info">
                <div><span>Departemen</span><div id="detailDepartemen">—</div></div>
                <div><span>Shift</span><div id="detailShift">—</div></div>
                <div><span>Tanggal lembur</span><div id="detailTanggal">—</div></div>
                <div><span>Jam lembur &amp; total jam</span><div id="detailJam">—</div></div>
            </div>

            <div class="lm-teks-grid">
                <div class="lm-kotak-teks"><span>Uraian pekerjaan</span><div id="detailUraian">—</div></div>
                <div class="lm-kotak-teks"><span>Alasan lembur</span><div id="detailAlasan">—</div></div>
            </div>

            <h4 class="lm-subjudul">Karyawan yang lembur <small id="detailJumlahKaryawan"></small></h4>
            <div class="lm-tabel-bungkus">
                <table class="lm-tabel">
                    <thead>
                        <tr>
                            <th style="width: 56px;">No</th>
                            <th>Nama Karyawan</th>
                            <th>Jabatan</th>
                            <th>Pekerjaan</th>
                        </tr>
                    </thead>
                    <tbody id="detailKaryawanBody"></tbody>
                </table>
            </div>
        </div>

        <div class="lm-kaki">
            <button type="button" class="rl-btn-putih lm-btn" id="btnTutupBawahLembur">Tutup</button>
            <a href="#" class="rl-btn-putih lm-btn" id="btnUbahLemburModal" style="display: none;">Ubah</a>
            <form method="POST" action="#" id="formAjukanLemburModal" style="display: none; margin: 0;" onsubmit="return confirm('Ajukan rencana lembur ini ke Kepala Produksi?');">
                @csrf
                <button type="submit" class="rl-btn-utama lm-btn">Ajukan Rencana</button>
            </form>
            <a href="#" class="rl-btn-utama lm-btn" id="btnRealisasiLemburModal" style="display: none;">Isi Realisasi</a>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('modalDetailLembur');
    if (!modal) return;

    const badgeWrap = document.getElementById('modalLemburStatusBadge');
    const pesanEl = document.getElementById('modalLemburPesan');
    const karyawanBody = document.getElementById('detailKaryawanBody');
    const stepper = document.getElementById('detailStepper');
    const btnUbah = document.getElementById('btnUbahLemburModal');
    const formAjukan = document.getElementById('formAjukanLemburModal');
    const btnRealisasi = document.getElementById('btnRealisasiLemburModal');

    const TAHAP = [
        ['Diajukan', 'Kepala Unit'],
        ['Diperiksa', 'Kepala Produksi'],
        ['Diketahui', 'Operasional Manager'],
        ['Keputusan', 'General Manager']
    ];

    function esc(teks) {
        return String(teks ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function bukaModal(d) {
        document.getElementById('detailNoPengajuan').textContent = d.no_pengajuan || '—';
        document.getElementById('detailDepartemen').textContent = d.departemen || '—';
        document.getElementById('detailShift').textContent = d.shift || '—';
        document.getElementById('detailTanggal').textContent = d.tanggal_lembur || '—';
        document.getElementById('detailJam').textContent = (d.jam_lembur || '—') + ' (' + (d.total_jam || '0 jam') + ')';
        document.getElementById('detailUraian').textContent = d.uraian_pekerjaan || '—';
        document.getElementById('detailAlasan').textContent = d.alasan_lembur || '—';

        const status = d.status || '';
        badgeWrap.innerHTML = status ? '<span class="rl-badge ' + esc(d.status_kelas || 'rl-abu') + '">' + esc(status) + '</span>' : '';

        // tahap persetujuan
        const langkah = Number(d.langkah || 0);
        stepper.innerHTML = TAHAP.map(function (t, i) {
            const n = i + 1;
            let kelas = '';
            if (n <= langkah) kelas = 'selesai';
            else if (n === langkah + 1) kelas = 'aktif';
            if (d.ditolak && n === 4) kelas = 'tolak';
            const isi = kelas === 'selesai' ? '✓' : (kelas === 'tolak' ? '✕' : n);
            return '<div class="lm-step ' + kelas + '"><div class="lm-bulat">' + isi + '</div>' +
                   '<div class="lm-nama">' + t[0] + '</div><div class="lm-peran">' + t[1] + '</div></div>';
        }).join('');

        // pesan sesuai status
        pesanEl.style.display = 'none';
        pesanEl.className = 'lm-pesan';
        if (status === 'Dikembalikan') {
            pesanEl.classList.add('kuning');
            pesanEl.innerHTML = '<b>Pengajuan dikembalikan.</b> ' + (d.catatan_tolak ? 'Catatan: ' + esc(d.catatan_tolak) : 'Perbaiki lalu ajukan kembali.');
            pesanEl.style.display = 'block';
        } else if (status === 'Ditolak') {
            pesanEl.classList.add('merah');
            pesanEl.innerHTML = '<b>Pengajuan ditolak.</b> ' + (d.catatan_tolak ? 'Catatan: ' + esc(d.catatan_tolak) : 'Buat pengajuan baru bila masih diperlukan.');
            pesanEl.style.display = 'block';
        } else if (status === 'Disetujui') {
            pesanEl.classList.add('hijau');
            pesanEl.textContent = 'Pengajuan telah disetujui. Isi realisasi setelah lembur dilaksanakan.';
            pesanEl.style.display = 'block';
        }

        // karyawan
        const daftar = Array.isArray(d.karyawan) ? d.karyawan : [];
        document.getElementById('detailJumlahKaryawan').textContent = '(' + daftar.length + ' orang)';
        karyawanBody.innerHTML = daftar.length === 0
            ? '<tr><td colspan="4" class="lm-kosong">Belum ada data karyawan.</td></tr>'
            : daftar.map(function (k, i) {
                return '<tr><td>' + (i + 1) + '</td><td>' + esc(k.nama) + '</td><td>' + esc(k.jabatan) + '</td><td>' + esc(k.pekerjaan) + '</td></tr>';
            }).join('');

        // tombol aksi
        if (d.boleh_ubah && d.url_edit) { btnUbah.href = d.url_edit; btnUbah.style.display = 'inline-flex'; }
        else { btnUbah.style.display = 'none'; }

        if (d.boleh_ubah && d.url_ajukan) { formAjukan.action = d.url_ajukan; formAjukan.style.display = 'inline-flex'; }
        else { formAjukan.style.display = 'none'; }

        if (d.bisa_realisasi && d.url_realisasi) { btnRealisasi.href = d.url_realisasi; btnRealisasi.style.display = 'inline-flex'; }
        else { btnRealisasi.style.display = 'none'; }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function tutupModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-buka-detail-lembur]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            try { bukaModal(JSON.parse(btn.getAttribute('data-detail') || '{}')); }
            catch (err) { console.error('Gagal memuat detail lembur:', err); }
        });
    });

    document.getElementById('tutupModalLembur')?.addEventListener('click', tutupModal);
    document.getElementById('btnTutupBawahLembur')?.addEventListener('click', tutupModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) tutupModal(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) tutupModal();
    });
})();
</script>

<style>
.rl-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 8px 28px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI',Inter,Arial,sans-serif;
}

.rl-page * {
    box-sizing: border-box;
}

.rl-stats {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.rl-stat {
    min-height: 95px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
    min-width: 0;
}

.rl-stat-angka {
    font-size: clamp(26px,2vw,34px);
    font-weight: 600;
    line-height: 1.1;
    margin-bottom: 7px;
    color: #000;
}

.rl-stat-label {
    font-size: 13px;
    color: #344054;
}

.rl-stat-ikon {
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    border-radius: 13px;
    background: #eef2f6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rl-stat-ikon svg {
    width: 23px;
    height: 23px;
    stroke: #111827;
    stroke-width: 1.8;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.rl-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 22px;
    padding: 24px 24px 22px;
}

.rl-kepala {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}

.rl-judul {
    font-size: 20px;
    font-weight: 500;
    margin: 0;
    color: #111;
}

.rl-btn-utama {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 44px;
    padding: 0 24px;
    border: 0;
    border-radius: 11px;
    background: #ff941c;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    font-family: inherit;
    text-decoration: none;
    cursor: pointer;
    transition: background .2s;
}

.rl-btn-utama:hover {
    background: #e58316;
}

.rl-plus {
    font-size: 22px;
    font-weight: 400;
    line-height: 1;
}

.rl-btn-putih {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 34px;
    padding: 0 16px;
    border: 1px solid #d0d5dd;
    border-radius: 9px;
    background: #fff;
    color: #111827;
    font-size: 13px;
    font-weight: 500;
    font-family: inherit;
    text-decoration: none;
    cursor: pointer;
    transition: background .2s,border-color .2s;
}

.rl-btn-putih:hover {
    background: #f8fafc;
    border-color: #98a2b3;
}

.rl-filter {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.rl-cari {
    height: 44px;
    width: 380px;
    max-width: 100%;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 14px;
    border: 1px solid #d0dbea;
    border-radius: 11px;
    background: #fff;
}

.rl-cari svg {
    width: 19px;
    height: 19px;
    flex-shrink: 0;
    stroke: #111827;
    stroke-width: 2;
    fill: none;
    stroke-linecap: round;
}

.rl-cari input {
    width: 100%;
    border: 0;
    outline: 0;
    background: transparent;
    font-size: 14px;
    font-family: inherit;
    color: #344054;
}

.rl-cari input::placeholder {
    color: #98a2b3;
}

.rl-select {
    height: 44px;
    width: 200px;
    padding: 0 42px 0 14px;
    border: 1px solid #d0dbea;
    border-radius: 11px;
    font-size: 14px;
    font-family: inherit;
    color: #111827;
    background: #fff;
    outline: none;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
}

.rl-tabel-bungkus {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #acb1b9;
    border-radius: 10px;
}

.rl-tabel {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
    white-space: nowrap;
}

.rl-tabel thead {
    background: #eef2f6;
}

.rl-tabel th {
    height: 48px;
    padding: 0 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 500;
    color: #111;
}

.rl-tabel td {
    height: 58px;
    padding: 0 16px;
    border-top: 1px solid #d8dee7;
    font-size: 14px;
    color: #111;
}

.rl-tebal {
    font-weight: 500;
}

.rl-kosong {
    text-align: center;
    color: #667085;
    height: 90px !important;
}

.rl-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 14px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}

.rl-abu {
    color: #475467;
    background: #eef0f3;
}

.rl-oranye {
    color: #9a4a00;
    background: rgba(255,207,172,.55);
}

.rl-biru {
    color: #1e56a0;
    background: #d6e9fb;
}

.rl-hijau {
    color: #16834f;
    background: #e6f4e9;
}

.rl-merah {
    color: #c62828;
    background: rgba(234,74,76,.1);
}

.rl-kuning {
    color: #b54708;
    background: #fef0c7;
}

.rl-paginasi {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 20px;
    font-size: 13px;
    color: #667085;
    flex-wrap: wrap;
}

.rl-paginasi-tombol {
    display: flex;
    align-items: center;
    gap: 6px;
}

.rl-paginasi-tombol a,
.rl-paginasi-tombol span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 8px;
    border: 1px solid #d0d5dd;
    border-radius: 9px;
    background: #fff;
    color: #111827;
    font-size: 13px;
    text-decoration: none;
    transition: .2s;
}

.rl-paginasi-tombol span.aktif {
    background: #ff941c;
    border-color: #ff941c;
    color: #fff;
}

.rl-paginasi-tombol span.nonaktif {
    background: #f8f9fa;
    color: #98a2b3;
    cursor: not-allowed;
}

.rl-paginasi-tombol a:hover {
    background: #f8fafc;
    border-color: #98a2b3;
}

/* Pop up detail */

.lm-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 18px;
    background: rgba(17,24,39,.55);
    backdrop-filter: blur(2px);
}

.lm-backdrop.is-open {
    display: flex;
}

.lm-modal {
    width: 100%;
    max-width: 820px;
    max-height: 92vh;
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(15,23,42,.22);
    color: #111827;
    font-family: 'Segoe UI',Inter,Arial,sans-serif;
}

.lm-kepala {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.lm-judul {
    margin: 0;
    font-size: 18px;
    font-weight: 500;
}

.lm-sub {
    margin-top: 3px;
    font-size: 13px;
    color: #667085;
}

.lm-kepala-kanan {
    display: flex;
    align-items: center;
    gap: 12px;
}

.lm-tutup {
    width: 34px;
    height: 34px;
    padding: 0;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    background: #fff;
    color: #374151;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
}

.lm-tutup:hover {
    border-color: #ff941c;
    color: #ff941c;
    background: #fffaf4;
}

.lm-tubuh {
    padding: 20px 24px;
    overflow-y: auto;
}

.lm-pesan {
    padding: 11px 14px;
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.5;
    margin-bottom: 18px;
}

.lm-pesan.kuning {
    background: #fffbeb;
    border: 1px solid #fcd34d;
    color: #92400e;
}

.lm-pesan.merah {
    background: #fef2f2;
    border: 1px solid #fca5a5;
    color: #991b1b;
}

.lm-pesan.hijau {
    background: #ecfdf5;
    border: 1px solid #6ee7b7;
    color: #065f46;
}

.lm-stepper {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    margin-bottom: 22px;
}

.lm-step {
    position: relative;
    text-align: center;
    min-width: 0;
}

.lm-step:not(:last-child)::after {
    content: "";
    position: absolute;
    top: 15px;
    left: calc(50% + 16px);
    width: calc(100% - 32px);
    height: 2px;
    background: #d8dee7;
}

.lm-step.selesai:not(:last-child)::after {
    background: #ff941c;
}

.lm-bulat {
    position: relative;
    z-index: 1;
    width: 30px;
    height: 30px;
    margin: 0 auto 8px;
    border: 2px solid #acb1b9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #6b7280;
    font-size: 14px;
}

.lm-step.selesai .lm-bulat {
    background: #ff941c;
    border-color: #ff941c;
    color: #fff;
}

.lm-step.aktif .lm-bulat {
    border-color: #ff941c;
    color: #ff941c;
}

.lm-step.tolak .lm-bulat {
    background: #c62828;
    border-color: #c62828;
    color: #fff;
}

.lm-nama {
    font-size: 13px;
    font-weight: 500;
}

.lm-peran {
    font-size: 12px;
    color: #6b7280;
    margin-top: 3px;
}

.lm-info {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 12px 24px;
    padding: 14px 18px;
    margin-bottom: 14px;
    border: 1px solid #e4e7ec;
    border-radius: 12px;
    font-size: 14px;
}

.lm-info span,
.lm-kotak-teks span {
    display: block;
    font-size: 12px;
    color: #667085;
    margin-bottom: 3px;
}

.lm-teks-grid {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 14px;
    margin-bottom: 18px;
}

.lm-kotak-teks {
    padding: 12px 16px;
    border: 1px solid #e4e7ec;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.5;
    white-space: pre-line;
}

.lm-subjudul {
    margin: 0 0 10px;
    font-size: 14px;
    font-weight: 500;
}

.lm-subjudul small {
    font-weight: 400;
    color: #667085;
}

.lm-tabel-bungkus {
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    overflow-x: auto;
}

.lm-tabel {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.lm-tabel th {
    height: 42px;
    padding: 0 14px;
    text-align: left;
    font-weight: 500;
    background: #eef2f6;
    color: #111;
}

.lm-tabel td {
    height: 44px;
    padding: 0 14px;
    border-top: 1px solid #e5e7eb;
    color: #1f2937;
}

.lm-kosong {
    text-align: center;
    color: #667085;
}

.lm-kaki {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    flex-wrap: wrap;
    padding: 14px 24px;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 0 0 20px 20px;
}

.lm-btn {
    height: 40px;
    padding: 0 20px;
}

@media(max-width:1100px) {
    .rl-stats {
        grid-template-columns: repeat(2,minmax(0,1fr));
    }
}

@media(max-width:700px) {
    .rl-stats {
        grid-template-columns: 1fr;
    }

    .rl-select {
        width: 100%;
    }

    .lm-info,
    .lm-teks-grid {
        grid-template-columns: 1fr;
    }

    .lm-stepper {
        min-width: 520px;
    }

    .lm-tubuh {
        overflow-x: auto;
    }
}
</style>
@endsection