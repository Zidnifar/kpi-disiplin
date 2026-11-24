@php
    $koleksiDaftar = $daftar ?? collect();
    $barisItem = $koleksiDaftar instanceof \Illuminate\Contracts\Pagination\Paginator
        || $koleksiDaftar instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ? collect($koleksiDaftar->items())
        : collect($koleksiDaftar);

    $ruteDetailTujuan = $ruteDetail ?? 'lembur.periksa.show';
    $statusAksiTarget = $statusAksi ?? \App\Models\RencanaLembur::MENUNGGU_PRODUKSI;
    $teksAksiUtama    = $labelAksi ?? 'Periksa';
@endphp

<div class="pl-table-wrap">
    <table class="pl-table">
        <thead>
            <tr>
                <th class="pl-col-no">No</th>
                <th>No. Pengajuan</th>
                <th>Diajukan oleh</th>
                <th>Unit</th>
                <th>Tanggal</th>
                <th class="pl-col-tengah">Karyawan</th>
                <th>Jam</th>
                <th>Status</th>
                <th class="pl-col-aksi">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($barisItem as $idx => $r)
            @php
                $nomorUrut = (method_exists($koleksiDaftar, 'firstItem') && $koleksiDaftar->firstItem())
                    ? $koleksiDaftar->firstItem() + $idx
                    : $idx + 1;

                $namaPengaju = $r->pengaju->nama_lengkap
                    ?? $r->pengaju->nama
                    ?? $r->pengaju->karyawan->nama_lengkap
                    ?? '—';

                $namaUnit = $r->departemen->nama_dept ?? '—';

                $jumlahKaryawan = method_exists($r, 'detail')
                    ? $r->detail->count()
                    : (int) ($r->jumlah_karyawan ?? 0);

                $totalJam = method_exists($r, 'totalJamRencana')
                    ? \App\Support\Format::jumlahJam((float) $r->totalJamRencana())
                    : \App\Support\Format::jumlahJam((float) ($r->total_jam ?? 0));

                $statusRaw = (string) ($r->status_rencana ?? '');
                $bisaAksiUtama = $statusRaw === $statusAksiTarget;

                [$teksBadge, $kelasBadge] = match ($statusRaw) {
                    \App\Models\RencanaLembur::MENUNGGU_PRODUKSI    => ['Menunggu pemeriksaan', 'badge-menunggu'],
                    \App\Models\RencanaLembur::MENUNGGU_OPERASIONAL => ['Menunggu diketahui', 'badge-menunggu'],
                    \App\Models\RencanaLembur::MENUNGGU_GM          => ['Menunggu keputusan', 'badge-menunggu'],
                    \App\Models\RencanaLembur::DIKEMBALIKAN         => ['Dikembalikan', 'badge-dikembalikan'],
                    \App\Models\RencanaLembur::DITOLAK              => ['Ditolak', 'badge-ditolak'],
                    \App\Models\RencanaLembur::DISETUJUI,
                    \App\Models\RencanaLembur::SELESAI              => ['Disetujui', 'badge-selesai'],
                    default                                         => [$statusRaw ?: 'Sudah diproses', 'badge-diketahui'],
                };

                if ($statusAksiTarget === \App\Models\RencanaLembur::MENUNGGU_OPERASIONAL && ! $bisaAksiUtama && $statusRaw !== \App\Models\RencanaLembur::DITOLAK) {
                    $teksBadge = 'Sudah diketahui';
                    $kelasBadge = 'badge-diketahui';
                } elseif ($statusAksiTarget === \App\Models\RencanaLembur::MENUNGGU_PRODUKSI && ! $bisaAksiUtama && $statusRaw !== \App\Models\RencanaLembur::DIKEMBALIKAN) {
                    $teksBadge = 'Sudah diperiksa';
                    $kelasBadge = 'badge-selesai';
                }
            @endphp
            <tr>
                <td class="pl-col-no">{{ $nomorUrut }}</td>
                <td><strong>{{ $r->no_pengajuan }}</strong></td>
                <td>{{ $namaPengaju }}</td>
                <td>{{ $namaUnit }}</td>
                <td>{{ \App\Support\Format::tanggal($r->tanggal_lembur) }}</td>
                <td class="pl-col-tengah">{{ $jumlahKaryawan }}</td>
                <td>{{ $totalJam }}</td>
                <td>
                    <span class="pl-badge {{ $kelasBadge }}">{{ $teksBadge }}</span>
                </td>
                <td class="pl-col-aksi">
                    @if ($bisaAksiUtama)
                        <a href="{{ route($ruteDetailTujuan, $r) }}" class="pl-btn-aksi-oren">{{ $teksAksiUtama }}</a>
                    @else
                        <a href="{{ route($ruteDetailTujuan, $r) }}" class="pl-btn-aksi-detail">Detail</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="pl-empty">Belum ada data rencana lembur.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if ($koleksiDaftar instanceof \Illuminate\Contracts\Pagination\Paginator || $koleksiDaftar instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
    <div class="pl-pagination-bar">
        <div class="pl-pagination-info">
            @if ($koleksiDaftar->total() > 0)
                Menampilkan {{ $koleksiDaftar->firstItem() }}–{{ $koleksiDaftar->lastItem() }} dari {{ $koleksiDaftar->total() }} data
            @else
                Tidak ada data
            @endif
        </div>

        @if ($koleksiDaftar->hasPages())
            <div class="pl-pagination-links">
                @if ($koleksiDaftar->onFirstPage())
                    <span class="pl-page-btn disabled">‹</span>
                @else
                    <a href="{{ $koleksiDaftar->previousPageUrl() }}" class="pl-page-btn">‹</a>
                @endif

                @foreach ($koleksiDaftar->getUrlRange(1, $koleksiDaftar->lastPage()) as $halaman => $urlHal)
                    @if ($halaman == $koleksiDaftar->currentPage())
                        <span class="pl-page-btn active">{{ $halaman }}</span>
                    @else
                        <a href="{{ $urlHal }}" class="pl-page-btn">{{ $halaman }}</a>
                    @endif
                @endforeach

                @if ($koleksiDaftar->hasMorePages())
                    <a href="{{ $koleksiDaftar->nextPageUrl() }}" class="pl-page-btn">›</a>
                @else
                    <span class="pl-page-btn disabled">›</span>
                @endif
            </div>
        @endif
    </div>
@endif