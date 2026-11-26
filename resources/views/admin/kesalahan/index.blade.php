@extends('layouts.app')

@section('title', 'Periksa Kesalahan')

@section('isi')
<div class="kesalahan-page" style="width: 100%; padding: 0 8px 24px; color: #101828; box-sizing: border-box;">
    <!-- Breadcrumb / Navigasi Halaman -->
    <div class="breadcrumb-area" style="margin-bottom: 20px; font-size: 13px; color: #667085;">
        <a href="{{ route('admin.absensi.index') }}" style="color: #667085; text-decoration: none;">Data Absensi</a>
        <span style="margin: 0 6px;">/</span>
        <span style="color: #101828; font-weight: 500;">Periksa Kesalahan</span>
    </div>

    <!-- Card Utama -->
    <div class="content-card" style="background: #fff; border: 1px solid #d0d5dd; border-radius: 20px; padding: 28px 32px; box-sizing: border-box;">
        <div class="form-header" style="margin-bottom: 24px;">
            <h2 style="font-size: 20px; font-weight: 700; color: #101828; margin: 0 0 4px;">Pemeriksaan Kesalahan Data Absensi</h2>
            <p style="font-size: 13px; color: #667085; margin: 0;">Periksa dan konfirmasi data absensi yang belum dikonfirmasi karyawan.</p>
        </div>

        <!-- Filter Bulan & Jenis Kesalahan -->
        <form method="GET" action="{{ route('admin.kesalahan.index') }}" id="filterKesalahanForm" style="display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
            @php
                $bulanAktif = $bulan ?? now()->format('Y-m');
            @endphp

            <select
                name="bulan"
                onchange="this.form.submit()"
                style="min-width: 185px; height: 44px; padding: 0 42px 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #344054; background-color: #fff; outline: none; cursor: pointer; -webkit-appearance: none; appearance: none; background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 16px center;"
            >
                <option value="{{ $bulanAktif }}" selected>
                    {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $bulanAktif)->translatedFormat('F Y') }}
                </option>
                @for ($i = 0; $i < 12; $i++)
                    @php
                        $opsiBulan = now()->subMonths($i)->format('Y-m');
                    @endphp
                    @if ($opsiBulan !== $bulanAktif)
                        <option value="{{ $opsiBulan }}">
                            {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $opsiBulan)->translatedFormat('F Y') }}
                        </option>
                    @endif
                @endfor
            </select>

            <select
                name="jenis"
                onchange="this.form.submit()"
                style="min-width: 185px; height: 44px; padding: 0 42px 0 14px; border: 1px solid #d0dbea; border-radius: 11px; font-size: 14px; color: #344054; background-color: #fff; outline: none; cursor: pointer; -webkit-appearance: none; appearance: none; background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 16px center;"
            >
                <option value="">Semua Jenis</option>
                @foreach ($jenisPilihan ?? [] as $jenis)
                    <option value="{{ $jenis }}" @selected(request('jenis') === $jenis)>{{ $jenis }}</option>
                @endforeach
            </select>
        </form>

        <!-- Tabel Data Kesalahan -->
        <div style="width: 100%; overflow-x: auto; border: 1px solid #d0d5dd; border-radius: 14px; background: #fff;">
            <table style="width: 100%; border-collapse: collapse; min-width: 720px; text-align: left;">
                <thead>
                    <tr style="background: #f2f4f7; border-bottom: 1px solid #d0d5dd;">
                        <th style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #101828; width: 64px;">No</th>
                        <th style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #101828;">Karyawan</th>
                        <th style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #101828;">Tanggal</th>
                        <th style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #101828;">Jenis Kesalahan</th>
                        <th style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #101828;">Status</th>
                        <th style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #101828; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $index => $item)
                        @php
                            $jenisLower = mb_strtolower($item->jenis_kesalahan ?? '');
                            $isPerluDicek = str_contains($jenisLower, 'dicek') || str_contains($jenisLower, 'shift') || str_contains($jenisLower, 'ganda');
                            $statusLabel = $isPerluDicek ? 'Perlu dicek' : 'Bermasalah';
                            $badgeBg = $isPerluDicek ? '#fef3c7' : '#fee4e2';
                            $badgeColor = $isPerluDicek ? '#b45309' : '#d92d20';
                        @endphp
                        <tr style="border-top: 1px solid #eaecf0;">
                            <td style="padding: 14px 16px; font-size: 14px; color: #667085;">
                                {{ method_exists($daftar, 'firstItem') && $daftar->firstItem() ? $daftar->firstItem() + $index : $index + 1 }}
                            </td>
                            <td style="padding: 14px 16px; font-size: 14px; font-weight: 600; color: #101828;">
                                {{ $item->absensi->karyawan->nama_karyawan ?? '—' }}
                            </td>
                            <td style="padding: 14px 16px; font-size: 14px; color: #344054;">
                                {{ \App\Support\Format::tanggal($item->absensi->tanggal_absen ?? now()) }}
                            </td>
                            <td style="padding: 14px 16px; font-size: 14px; color: #344054;">
                                {{ $item->jenis_kesalahan ?? '—' }}
                            </td>
                            <td style="padding: 14px 16px; font-size: 14px;">
                                <span style="display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; background: {{ $badgeBg }}; color: {{ $badgeColor }};">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td style="padding: 14px 16px; font-size: 14px;">
                                @if ($item->absensi)
                                    <a
                                        href="{{ route('admin.kesalahan.konfirmasi', $item->absensi) }}"
                                        style="display: inline-flex; align-items: center; justify-content: center; height: 36px; padding: 0 18px; border-radius: 10px; background: #ff941c; color: #fff; font-size: 13px; font-weight: 600; text-decoration: none;"
                                    >
                                        Konfirmasi
                                    </a>
                                @else
                                    <span style="color: #667085;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 32px 16px; text-align: center; font-size: 14px; color: #667085;">
                                Tidak ada data kesalahan yang perlu diperiksa pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer Info & Pagination -->
        @if (method_exists($daftar, 'total'))
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-top: 20px; font-size: 13px; color: #667085;">
                <div>
                    Menampilkan {{ $daftar->firstItem() ?? 0 }}–{{ $daftar->lastItem() ?? 0 }} dari {{ $daftar->total() }} data
                </div>
                <div>
                    {{ $daftar->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection