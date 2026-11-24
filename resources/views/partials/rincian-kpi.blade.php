{{-- Rincian poin pengurang satu hasil KPI. Butuh $hasil dengan relasi detail.indikator. --}}
<div class="tabel-bungkus">
    <table class="tabel">
        <thead><tr><th>Kode</th><th>Kriteria</th><th class="tengah">Frekuensi</th><th class="tengah">Bobot (poin)</th><th class="kanan">Total Pengurang</th></tr></thead>
        <tbody>
        @forelse ($hasil->detail->sortBy(fn ($d) => $d->indikator->kode_indikator) as $d)
            <tr>
                <td><strong>{{ $d->indikator->kode_indikator }}</strong></td>
                <td>{{ $d->indikator->nama_indikator }}</td>
                <td class="tengah">{{ $d->frekuensi }}</td>
                <td class="tengah">{{ $d->bobot_poin }}</td>
                <td class="kanan">− {{ $d->total_pengurang }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="kosong">Tidak ada pelanggaran pada periode ini. Nilai penuh.</td></tr>
        @endforelse
        </tbody>
        <tfoot><tr><td colspan="4">Total</td><td class="kanan">− {{ $hasil->total_pengurang }}</td></tr></tfoot>
    </table>
</div>
