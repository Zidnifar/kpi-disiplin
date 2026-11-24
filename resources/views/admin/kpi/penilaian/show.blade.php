@extends('layouts.app')

@section('title', 'Detail Penilaian')
@section('judul', 'Detail Penilaian')

@section('isi')
@php
    $kelasPredikat = match (strtolower((string) $predikat)) {
        'sangat baik' => ['#e6f4e9', '#16834f'],
        'baik'        => ['#dbeafe', '#1d4ed8'],
        'cukup'       => ['#fde8d8', '#9a3412'],
        'kurang'      => ['#fee4e2', '#b42318'],
        default       => ['#f2f4f7', '#475467'],
    };
    $statusTeks = $hasil ? ($hasil->status_validasi === 'Tervalidasi' ? 'Disetujui' : $hasil->status_validasi) : 'Belum dihitung';
    $statusWarna = $hasil && $hasil->status_validasi === 'Tervalidasi' ? ['#dbeafe', '#1d4ed8'] : ['#fde8d8', '#9a3412'];
    $thStyle = 'height: 42px; background: #f8f9fa; color: #344054; font-size: 13px; font-weight: 400; text-align: center; padding: 0 14px; border-bottom: 1px solid #d0d5dd;';
    $tdStyle = 'height: 46px; padding: 0 14px; border-bottom: 1px solid #eef2f6; font-size: 13px; font-weight: 400; color: #101828; text-align: center;';
@endphp

<div id="konten-detail-penilaian" style="background: #fff; border-radius: 16px; border: 1px solid #d0d5dd; padding: 22px 24px; color: #101828; max-height: 90vh; overflow-y: auto;">

    <!-- Kepala -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 16px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 400; margin: 0 0 4px;">Detail Penilaian</h2>
            <p style="font-size: 13px; color: #667085; margin: 0;">{{ $periode->nama_periode }}</p>
        </div>
        <button type="button" onclick="if (typeof tutupModalDetail === 'function') { tutupModalDetail(); } else { history.back(); }" aria-label="Tutup" style="width: 32px; height: 32px; padding: 0; border: 1px solid #d0dbea; border-radius: 8px; background: #fff; color: #344054; font-size: 18px; line-height: 1; cursor: pointer;">×</button>
    </div>

    <!-- Identitas karyawan -->
    <div style="border: 1px solid #e4e7ec; border-radius: 12px; padding: 14px 18px; margin-bottom: 16px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 24px; font-size: 13px;">
        <div><span style="color: #667085;">Nama</span><br>{{ $karyawan->nama_karyawan }}</div>
        <div><span style="color: #667085;">NIP</span><br>{{ $karyawan->nip ?? '—' }}</div>
        <div><span style="color: #667085;">Departemen</span><br>{{ $karyawan->departemen->nama_dept ?? '—' }}</div>
        <div><span style="color: #667085;">Jabatan</span><br>{{ $karyawan->jabatan ?? '—' }}</div>
    </div>

    <!-- Ringkasan nilai -->
    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px;">
        <div style="border: 1px solid #e4e7ec; border-radius: 12px; padding: 12px 16px;">
            <div style="font-size: 24px; line-height: 1.2;">{{ $nilai !== null ? \App\Support\Format::nilai($nilai) : '—' }}</div>
            <div style="font-size: 12px; color: #667085; margin-top: 2px;">Nilai akhir</div>
        </div>
        <div style="border: 1px solid #e4e7ec; border-radius: 12px; padding: 12px 16px;">
            <div style="margin-top: 4px;">
                @if ($predikat)
                    <span style="display: inline-flex; padding: 4px 12px; border-radius: 20px; font-size: 12px; background: {{ $kelasPredikat[0] }}; color: {{ $kelasPredikat[1] }};">{{ $predikat }}</span>
                @else
                    —
                @endif
            </div>
            <div style="font-size: 12px; color: #667085; margin-top: 8px;">Kategori</div>
        </div>
        <div style="border: 1px solid #e4e7ec; border-radius: 12px; padding: 12px 16px;">
            <div style="margin-top: 4px;">
                <span style="display: inline-flex; padding: 4px 12px; border-radius: 20px; font-size: 12px; background: {{ $statusWarna[0] }}; color: {{ $statusWarna[1] }};">{{ $statusTeks }}</span>
            </div>
            <div style="font-size: 12px; color: #667085; margin-top: 8px;">Status</div>
        </div>
    </div>

    <!-- Rincian per bulan -->
    <h3 style="font-size: 14px; font-weight: 400; margin: 0 0 8px;">Rincian kehadiran per bulan</h3>
    <div style="border: 1px solid #d0d5dd; border-radius: 6px; overflow-x: auto; margin-bottom: 16px;">
        <table style="width: 100%; border-collapse: collapse; white-space: nowrap;">
            <thead>
                <tr>
                    <th style="{{ $thStyle }}">Bulan</th>
                    <th style="{{ $thStyle }}">Hadir</th>
                    <th style="{{ $thStyle }}">Terlambat</th>
                    <th style="{{ $thStyle }}">Belum dikonfirmasi</th>
                    <th style="{{ $thStyle }}">Pengurang poin</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bulan as $nomor => $nama)
                    @php
                        $r = $rincian[$nomor] ?? ['hadir' => 0, 'terlambat' => 0, 'belum' => 0];
                        $poin = $poinBulan[$nomor] ?? 0;
                    @endphp
                    <tr>
                        <td style="{{ $tdStyle }}">{{ $nama }}</td>
                        <td style="{{ $tdStyle }}">{{ $r['hadir'] }}</td>
                        <td style="{{ $tdStyle }}">{{ $r['terlambat'] }}</td>
                        <td style="{{ $tdStyle }} {{ $r['belum'] > 0 ? 'color: #9a3412;' : '' }}">{{ $r['belum'] }}</td>
                        <td style="{{ $tdStyle }}">{{ $poin > 0 ? '− ' . $poin : '0' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if (($belumTotal ?? 0) > 0)
        <div style="background: #fbe9dd; color: #9a3412; padding: 10px 16px; border-radius: 12px; font-size: 13px; margin-bottom: 16px;">
            Ada {{ $belumTotal }} data kehadiran yang belum dikonfirmasi, sehingga nilai bisa berubah setelah dikonfirmasi.
            <a href="{{ route('admin.kesalahan.index', ['bulan' => $periode->tanggal_mulai->format('Y-m')]) }}" style="color: #9a3412; text-decoration: underline; margin-left: 4px;">Periksa kesalahan absensi</a>
        </div>
    @endif

    <!-- Aksi -->
    <div style="display: flex; justify-content: flex-end; gap: 10px;">
        @if ($hasil)
            <a href="{{ route('admin.kpi.hasil.show', $hasil) }}" style="height: 40px; padding: 0 18px; border: 1px solid #d0d5dd; border-radius: 10px; background: #fff; color: #344054; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center;">Buka di Hasil KPI</a>
        @endif
        <button type="button" onclick="if (typeof tutupModalDetail === 'function') { tutupModalDetail(); } else { history.back(); }" style="height: 40px; padding: 0 20px; border: 0; border-radius: 10px; background: #ff941c; color: #fff; font-size: 14px; cursor: pointer;">Tutup</button>
    </div>
</div>
@endsection