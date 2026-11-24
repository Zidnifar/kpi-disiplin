@extends('layouts.app')
@section('title', 'Shift Kerja')
@section('judul', 'Shift Kerja')

@section('isi')
<div class="shift-page" style="width: 100%; padding: 0 8px 24px; color: #101828;">
    
    <!-- 4 Kartu Statistik Dinamis -->
    <div class="summary-grid" style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; margin-bottom: 24px;">
        <div class="summary-card" style="min-height: 96px; background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;">
            <div class="summary-info">
                <h2 style="font-size: 34px; line-height: 1.1; font-weight: 700; color: #000; margin: 0 0 5px;">{{ $totalShift ?? 0 }}</h2>
                <p style="font-size: 13px; color: #344054; margin: 0;">Shift Kerja</p>
            </div>
        </div>
        <div class="summary-card" style="min-height: 96px; background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;">
            <div class="summary-info">
                <h2 style="font-size: 34px; line-height: 1.1; font-weight: 700; color: #000; margin: 0 0 5px;">{{ $totalKaryawanTerjadwal ?? 0 }}</h2>
                <p style="font-size: 13px; color: #344054; margin: 0;">Karyawan Terjadwal</p>
            </div>
        </div>
        <div class="summary-card" style="min-height: 96px; background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;">
            <div class="summary-info">
                <h2 style="font-size: 34px; line-height: 1.1; font-weight: 700; color: #000; margin: 0 0 5px;">{{ $totalShiftPagi ?? 0 }}</h2>
                <p style="font-size: 13px; color: #344054; margin: 0;">Shift pagi</p>
            </div>
        </div>
        <div class="summary-card" style="min-height: 96px; background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;">
            <div class="summary-info">
                <h2 style="font-size: 34px; line-height: 1.1; font-weight: 700; color: #000; margin: 0 0 5px;">{{ $totalShiftMalam ?? 0 }}</h2>
                <p style="font-size: 13px; color: #344054; margin: 0;">Shift malam</p>
            </div>
        </div>
    </div>

    <!-- Kotak Bagian 1: Daftar Shift Kerja -->
    <div class="content-card" style="background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; overflow: hidden; margin-bottom: 24px;">
        <div class="content-heading" style="min-height: 70px; padding: 18px 23px 10px; display: flex; align-items: center; justify-content: space-between; gap: 15px;">
            <h2 style="font-size: 20px; font-weight: 600; color: #101828; margin: 0;">Daftar shift kerja</h2>
            <a href="{{ route('admin.shift.create') }}" class="btn-tambah" style="height: 44px; padding: 0 21px; border: 0; border-radius: 11px; background: #ff941c; color: #fff; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 9px; text-decoration: none; cursor: pointer;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah shift
            </a>
        </div>

        <div class="table-wrapper" style="width: calc(100% - 46px); margin: 0 23px 20px; overflow-x: auto; border: 1px solid #d0d5dd; border-radius: 14px;">
            <table class="absensi-table" style="width: 100%; border-collapse: collapse; white-space: nowrap;">
                <thead>
                    <tr>
                        <th class="column-no" style="width: 60px; text-align: center; height: 48px; background: #f8f9fa; color: #344054; font-size: 13px; font-weight: 600; padding: 0 20px; border-bottom: 1px solid #d0d5dd;">No</th>
                        <th style="height: 48px; background: #f8f9fa; color: #344054; font-size: 13px; font-weight: 600; text-align: left; padding: 0 20px; border-bottom: 1px solid #d0d5dd;">Nama shift</th>
                        <th style="height: 48px; background: #f8f9fa; color: #344054; font-size: 13px; font-weight: 600; text-align: left; padding: 0 20px; border-bottom: 1px solid #d0d5dd;">Jam mulai</th>
                        <th style="height: 48px; background: #f8f9fa; color: #344054; font-size: 13px; font-weight: 600; text-align: left; padding: 0 20px; border-bottom: 1px solid #d0d5dd;">Jam selesai</th>
                        <th style="text-align: center; width: 100px; height: 48px; background: #f8f9fa; color: #344054; font-size: 13px; font-weight: 600; padding: 0 20px; border-bottom: 1px solid #d0d5dd;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($daftar as $index => $s)
                    <tr>
                        <td class="column-no" style="text-align: center; height: 60px; padding: 0 20px; border-bottom: 1px solid #eef2f6; color: #667085;">{{ $daftar->firstItem() + $index }}</td>
                        <td style="height: 60px; padding: 0 20px; border-bottom: 1px solid #eef2f6; font-size: 14px; font-weight: 500; color: #101828;">{{ $s->nama_shift }}</td>
                        <td style="height: 60px; padding: 0 20px; border-bottom: 1px solid #eef2f6; font-size: 14px; color: #101828;">{{ \App\Support\Format::jam($s->jam_mulai) }}</td>
                        <td style="height: 60px; padding: 0 20px; border-bottom: 1px solid #eef2f6; font-size: 14px; color: #101828;">{{ \App\Support\Format::jam($s->jam_selesai) }}</td>
                        <td style="text-align: center; height: 60px; padding: 0 20px; border-bottom: 1px solid #eef2f6;">
                            <a href="{{ route('admin.shift.edit', $s) }}" class="btn-action" title="Ubah" style="width: 36px; height: 36px; border: 1px solid #d0dbea; border-radius: 10px; background: #fff; color: #344054; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 17px; height: 17px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-data" style="text-align: center; height: 90px; color: #667085;">Belum ada shift.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Kotak Bagian 2: Jadwal Shift Hari Ini -->
    <div class="content-card" style="background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; overflow: hidden;">
        <div class="content-heading" style="min-height: 70px; padding: 18px 23px 10px; display: flex; align-items: center; justify-content: space-between; gap: 15px;">
            <h2 style="font-size: 20px; font-weight: 600; color: #101828; margin: 0;">Jadwal shift hari ini</h2>
            
            <!-- Form Filter Shift untuk Tabel Jadwal -->
            <form method="GET" action="{{ route('admin.shift.index') }}" style="width: 200px;">
                <select name="filter_shift" class="custom-select" onchange="this.form.submit()">
                    <option value="">Semua Shift</option>
                    @foreach ($daftar as $s)
                        <option value="{{ $s->id_shift }}" @selected(request('filter_shift') == $s->id_shift)>{{ $s->nama_shift }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="table-wrapper" style="width: calc(100% - 46px); margin: 0 23px 20px; overflow-x: auto; border: 1px solid #d0d5dd; border-radius: 14px;">
            <table class="absensi-table" style="width: 100%; border-collapse: collapse; white-space: nowrap;">
                <thead>
                    <tr>
                        <th class="column-no" style="width: 60px; text-align: center; height: 48px; background: #f8f9fa; color: #344054; font-size: 13px; font-weight: 600; padding: 0 20px; border-bottom: 1px solid #d0d5dd;">No</th>
                        <th style="height: 48px; background: #f8f9fa; color: #344054; font-size: 13px; font-weight: 600; text-align: left; padding: 0 20px; border-bottom: 1px solid #d0d5dd;">Nama karyawan</th>
                        <th style="height: 48px; background: #f8f9fa; color: #344054; font-size: 13px; font-weight: 600; text-align: left; padding: 0 20px; border-bottom: 1px solid #d0d5dd;">Shift</th>
                        <th style="height: 48px; background: #f8f9fa; color: #344054; font-size: 13px; font-weight: 600; text-align: left; padding: 0 20px; border-bottom: 1px solid #d0d5dd;">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($jadwalHariIni ?? [] as $index => $j)
                    <tr>
                        <td style="text-align: center; height: 60px; padding: 0 20px; border-bottom: 1px solid #eef2f6; color: #667085;">{{ $jadwalHariIni->firstItem() + $index }}</td>
                        <td style="height: 60px; padding: 0 20px; border-bottom: 1px solid #eef2f6; font-weight: 500; color: #101828;">{{ $j->karyawan->nama_karyawan ?? '—' }}</td>
                        <td style="height: 60px; padding: 0 20px; border-bottom: 1px solid #eef2f6; color: #101828;">{{ $j->jadwal?->shift?->nama_shift ?? ($j->shift->nama_shift ?? '—') }}</td>
                        <td style="height: 60px; padding: 0 20px; border-bottom: 1px solid #eef2f6; color: #101828;">{{ \App\Support\Format::tanggal($j->tanggal_absen ?? $j->tanggal) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-data" style="text-align: center; height: 90px; color: #667085;">Belum ada jadwal shift untuk hari ini. Silakan lakukan impor data absensi terlebih dahulu.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi khusus Jadwal Shift -->
        @if(isset($jadwalHariIni) && $jadwalHariIni->hasPages())
        <div class="pagination-area" style="padding: 0 23px 20px;">
            {{ $jadwalHariIni->links() }}
        </div>
        @endif
    </div>
</div>

<style>
.custom-select {
    height: 44px;
    width: 100%;
    border: 1px solid #d0dbea;
    border-radius: 11px;
    padding: 0 40px 0 14px;
    background: #fff;
    color: #101828;
    font-size: 14px;
    outline: none;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
}
.btn-action:hover { background: #f8fafc; border-color: #98a2b3; }
.btn-tambah:hover { background: #e58316; }
</style>
@endsection