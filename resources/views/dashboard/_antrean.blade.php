<div class="kartu">
    <div class="kartu-kepala">
        <h2>{{ $judul }}</h2>
        <a href="{{ route($ruteIndex) }}" class="tombol tombol-sekunder tombol-kecil">Lihat Semua</a>
    </div>
    <div class="tabel-bungkus">
        <table class="tabel">
            <thead><tr><th>No. Pengajuan</th><th>Unit</th><th>Tanggal Lembur</th><th class="tengah">Karyawan</th><th class="kanan">Total Jam</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse ($menunggu as $r)
                <tr>
                    <td><strong>{{ $r->no_pengajuan }}</strong></td>
                    <td>{{ $r->departemen->nama_dept ?? '—' }}</td>
                    <td>{{ \App\Support\Format::tanggal($r->tanggal_lembur) }}</td>
                    <td class="tengah">{{ $r->detail->count() }}</td>
                    <td class="kanan">{{ \App\Support\Format::jumlahJam($r->totalJamRencana()) }}</td>
                    <td><a class="tombol tombol-utama tombol-kecil" href="{{ route($ruteDetail, $r) }}">Buka</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="kosong">Tidak ada pengajuan yang menunggu.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
