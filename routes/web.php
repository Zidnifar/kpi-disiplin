<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanKpiController;
use App\Http\Controllers\Lembur;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\Saya;
use App\Http\Controllers\Unit;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'tampilkanForm'])->name('login');
    Route::post('/login', [LoginController::class, 'masuk'])->middleware('throttle:10,1')->name('login.proses');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::post('/logout', [LoginController::class, 'keluar'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [ProfilController::class, 'tampil'])->name('profil');
    Route::get('/profil/password', [ProfilController::class, 'formPassword'])->name('profil.password');
    Route::put('/profil/password', [ProfilController::class, 'ubahPassword'])->name('profil.password.update');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('pengguna', [Admin\PenggunaController::class, 'index'])->name('pengguna.index');
        Route::get('pengguna/tambah', [Admin\PenggunaController::class, 'create'])->name('pengguna.create');
        Route::post('pengguna', [Admin\PenggunaController::class, 'store'])->name('pengguna.store');
        Route::get('pengguna/{pengguna}/ubah', [Admin\PenggunaController::class, 'edit'])->name('pengguna.edit');
        Route::put('pengguna/{pengguna}', [Admin\PenggunaController::class, 'update'])->name('pengguna.update');
        Route::delete('pengguna/{pengguna}', [Admin\PenggunaController::class, 'destroy'])->name('pengguna.destroy');

        Route::get('karyawan', [Admin\KaryawanController::class, 'index'])->name('karyawan.index');
        Route::get('karyawan/tambah', [Admin\KaryawanController::class, 'create'])->name('karyawan.create');
        Route::post('karyawan', [Admin\KaryawanController::class, 'store'])->name('karyawan.store');
        Route::get('karyawan/{karyawan}/ubah', [Admin\KaryawanController::class, 'edit'])->name('karyawan.edit');
        Route::put('karyawan/{karyawan}', [Admin\KaryawanController::class, 'update'])->name('karyawan.update');
        Route::delete('/karyawan/{karyawan}', [Admin\KaryawanController::class, 'destroy'])->name('karyawan.destroy');

        Route::get('shift', [Admin\ShiftController::class, 'index'])->name('shift.index');
        Route::get('shift/tambah', [Admin\ShiftController::class, 'create'])->name('shift.create');
        Route::post('shift', [Admin\ShiftController::class, 'store'])->name('shift.store');
        Route::get('shift/{shift}/ubah', [Admin\ShiftController::class, 'edit'])->name('shift.edit');
        Route::put('shift/{shift}', [Admin\ShiftController::class, 'update'])->name('shift.update');

        Route::get('absensi', [Admin\AbsensiController::class, 'index'])->name('absensi.index');
        Route::get('absensi/impor', [Admin\AbsensiController::class, 'formImpor'])->name('absensi.impor');
        Route::post('absensi/impor/pratinjau', [Admin\AbsensiController::class, 'pratinjau'])->name('absensi.pratinjau');
        Route::post('absensi/impor', [Admin\AbsensiController::class, 'proses'])->name('absensi.proses');

        Route::get('kesalahan', [Admin\KesalahanAbsensiController::class, 'index'])->name('kesalahan.index');
        Route::get('konfirmasi', [Admin\KesalahanAbsensiController::class, 'daftarKonfirmasi'])->name('konfirmasi.index');
        Route::get('kesalahan/{absensi}', [Admin\KesalahanAbsensiController::class, 'konfirmasi'])->name('kesalahan.konfirmasi');
        Route::put('kesalahan/{absensi}', [Admin\KesalahanAbsensiController::class, 'simpan'])->name('kesalahan.simpan');

        Route::get('verifikasi', [Admin\VerifikasiKonfirmasiController::class, 'index'])->name('verifikasi.index');
        Route::get('verifikasi/{konfirmasi}', [Admin\VerifikasiKonfirmasiController::class, 'show'])->name('verifikasi.show');
        Route::post('verifikasi/{konfirmasi}/verifikasi', [Admin\VerifikasiKonfirmasiController::class, 'verifikasi'])->name('verifikasi.verifikasi');
        Route::post('verifikasi/{konfirmasi}/tolak', [Admin\VerifikasiKonfirmasiController::class, 'tolak'])->name('verifikasi.tolak');

        Route::get('kpi/penilaian', [Admin\PenilaianKpiController::class, 'index'])->name('kpi.penilaian.index');
        Route::post('kpi/penilaian/hitung', [Admin\PenilaianKpiController::class, 'hitung'])->name('kpi.penilaian.hitung');

        Route::get('kpi/periode', [Admin\PeriodeController::class, 'index'])->name('kpi.periode.index');
        Route::get('kpi/periode/tambah', [Admin\PeriodeController::class, 'create'])->name('kpi.periode.create');
        Route::post('kpi/periode', [Admin\PeriodeController::class, 'store'])->name('kpi.periode.store');
        Route::get('kpi/periode/{periode}/ubah', [Admin\PeriodeController::class, 'edit'])->name('kpi.periode.edit');
        Route::put('kpi/periode/{periode}', [Admin\PeriodeController::class, 'update'])->name('kpi.periode.update');
        Route::delete('kpi/periode/{periode}', [Admin\PeriodeController::class, 'destroy'])->name('kpi.periode.destroy');
        Route::get('kpi/periode/{periode}/hitung', [Admin\HitungKpiController::class, 'show'])->name('kpi.periode.hitung');
        Route::post('kpi/periode/{periode}/hitung', [Admin\HitungKpiController::class, 'proses'])->name('kpi.periode.hitung.proses');

        Route::get('kpi/indikator', [Admin\IndikatorKpiController::class, 'index'])->name('kpi.indikator.index');
        Route::get('kpi/indikator/tambah', [Admin\IndikatorKpiController::class, 'create'])->name('kpi.indikator.create');
        Route::post('kpi/indikator', [Admin\IndikatorKpiController::class, 'store'])->name('kpi.indikator.store');
        Route::get('kpi/indikator/{indikator}/ubah', [Admin\IndikatorKpiController::class, 'edit'])->name('kpi.indikator.edit');
        Route::put('kpi/indikator/{indikator}', [Admin\IndikatorKpiController::class, 'update'])->name('kpi.indikator.update');
        Route::delete('kpi/indikator/{indikator}', [Admin\IndikatorKpiController::class, 'destroy'])->name('kpi.indikator.destroy');

        Route::get('kpi/hasil', [Admin\HasilKpiController::class, 'index'])->name('kpi.hasil.index');
        Route::get('kpi/hasil/{hasil}', [Admin\HasilKpiController::class, 'show'])->name('kpi.hasil.show');
        Route::post('kpi/hasil/{hasil}/validasi', [Admin\HasilKpiController::class, 'validasi'])->name('kpi.hasil.validasi');
        Route::post('kpi/hasil/{hasil}/kembalikan', [Admin\HasilKpiController::class, 'kembalikan'])->name('kpi.hasil.kembalikan');
        Route::post('kpi/periode/{periode}/validasi-semua', [Admin\HasilKpiController::class, 'validasiSemua'])->name('kpi.hasil.validasi-semua');

        Route::get('kpi/rekap', [Admin\RekapKpiController::class, 'index'])->name('kpi.rekap');
        Route::get('kpi/rekap/excel', [Admin\RekapKpiController::class, 'excel'])->name('kpi.rekap.excel');

        Route::get('laporan', [LaporanKpiController::class, 'index'])->name('laporan');
        Route::get('laporan/cetak', [LaporanKpiController::class, 'cetak'])->name('laporan.cetak');
        Route::get('laporan/excel', [LaporanKpiController::class, 'excel'])->name('laporan.excel');

        Route::get('lembur', [Admin\RekapLemburController::class, 'index'])->name('lembur.index');
        Route::get('lembur/excel', [Admin\RekapLemburController::class, 'excel'])->name('lembur.excel');
    });

    // ------------------------------------------------------------------ FITUR KARYAWAN ("saya") — semua peran yang punya data karyawan
    Route::middleware('role:karyawan,kepala_unit,kepala_produksi,operasional_manager,general_manager')
        ->prefix('saya')->name('saya.')->group(function () {
            Route::get('kehadiran', [Saya\KehadiranController::class, 'index'])->name('kehadiran');
            Route::get('konfirmasi', [Saya\KonfirmasiController::class, 'index'])->name('konfirmasi');
            Route::get('konfirmasi/{absensi}', [Saya\KonfirmasiController::class, 'form'])->name('konfirmasi.form');
            Route::post('konfirmasi/{absensi}', [Saya\KonfirmasiController::class, 'kirim'])->name('konfirmasi.kirim');

            Route::get('kpi', [Saya\KpiController::class, 'nilai'])->name('kpi');
            Route::get('kpi/rincian', [Saya\KpiController::class, 'rincian'])->name('kpi.rincian');
            Route::get('kpi/riwayat', [Saya\KpiController::class, 'riwayat'])->name('kpi.riwayat');

            Route::get('lembur', [Saya\LemburController::class, 'index'])->name('lembur');
            Route::get('lembur/{rencana}', [Saya\LemburController::class, 'show'])->name('lembur.show');
        });

    // ------------------------------------------------------------------ KEPALA UNIT
    Route::middleware('role:kepala_unit')->prefix('unit')->name('unit.')->group(function () {
        Route::get('karyawan', [Unit\KaryawanController::class, 'index'])->name('karyawan.index');
        Route::get('kehadiran', [Unit\KehadiranController::class, 'index'])->name('kehadiran');

        Route::get('kpi/pemeriksaan', [Unit\PemeriksaanKpiController::class, 'index'])->name('kpi.pemeriksaan');
        Route::get('kpi/pemeriksaan/{hasil}', [Unit\PemeriksaanKpiController::class, 'show'])->name('kpi.pemeriksaan.show');
        Route::post('kpi/pemeriksaan/{hasil}', [Unit\PemeriksaanKpiController::class, 'tandai'])->name('kpi.pemeriksaan.tandai');

        Route::get('kpi/nilai', [Unit\NilaiKpiController::class, 'index'])->name('kpi.nilai');
        Route::get('kpi/nilai/excel', [Unit\NilaiKpiController::class, 'excel'])->name('kpi.nilai.excel');
        Route::get('kpi/rekap', [Unit\RekapKpiController::class, 'index'])->name('kpi.rekap');

        Route::get('lembur', [Unit\RencanaLemburController::class, 'index'])->name('lembur.index');
        Route::get('lembur/buat', [Unit\RencanaLemburController::class, 'create'])->name('lembur.create');
        Route::post('lembur', [Unit\RencanaLemburController::class, 'store'])->name('lembur.store');
        Route::get('lembur/{rencana}', [Unit\RencanaLemburController::class, 'show'])->name('lembur.show');
        Route::get('lembur/{rencana}/ubah', [Unit\RencanaLemburController::class, 'edit'])->name('lembur.edit');
        Route::put('lembur/{rencana}', [Unit\RencanaLemburController::class, 'update'])->name('lembur.update');
        Route::post('lembur/{rencana}/ajukan', [Unit\RencanaLemburController::class, 'ajukan'])->name('lembur.ajukan');

        Route::get('realisasi', [Unit\RealisasiLemburController::class, 'index'])->name('realisasi.index');
        Route::get('realisasi/{rencana}', [Unit\RealisasiLemburController::class, 'edit'])->name('realisasi.edit');
        Route::put('realisasi/{rencana}', [Unit\RealisasiLemburController::class, 'update'])->name('realisasi.update');

        Route::get('laporan', [LaporanKpiController::class, 'index'])->name('laporan');
        Route::get('laporan/cetak', [LaporanKpiController::class, 'cetak'])->name('laporan.cetak');
        Route::get('laporan/excel', [LaporanKpiController::class, 'excel'])->name('laporan.excel');
    });

    // ------------------------------------------------------------------ PERSETUJUAN LEMBUR (tiga tahap)
    Route::middleware('role:kepala_produksi')->prefix('lembur/periksa')->name('lembur.periksa.')->group(function () {
        Route::get('/', [Lembur\PemeriksaanController::class, 'index'])->name('index');
        Route::get('{rencana}', [Lembur\PemeriksaanController::class, 'show'])->name('show');
        Route::post('{rencana}', [Lembur\PemeriksaanController::class, 'periksa'])->name('periksa');
        Route::post('{rencana}/kembalikan', [Lembur\PemeriksaanController::class, 'kembalikan'])->name('kembalikan');
    });

    Route::middleware('role:operasional_manager')->prefix('lembur/ketahui')->name('lembur.ketahui.')->group(function () {
        Route::get('/', [Lembur\KetahuiController::class, 'index'])->name('index');
        Route::get('{rencana}', [Lembur\KetahuiController::class, 'show'])->name('show');
        Route::post('{rencana}', [Lembur\KetahuiController::class, 'tandai'])->name('tandai');
    });

    Route::middleware('role:general_manager')->prefix('lembur/putuskan')->name('lembur.putuskan.')->group(function () {
        Route::get('/', [Lembur\PutusanController::class, 'index'])->name('index');
        Route::get('{rencana}', [Lembur\PutusanController::class, 'show'])->name('show');
        Route::post('{rencana}/setujui', [Lembur\PutusanController::class, 'setujui'])->name('setujui');
        Route::get('{rencana}/tolak', [Lembur\PutusanController::class, 'formTolak'])->name('tolak');
        Route::post('{rencana}/tolak', [Lembur\PutusanController::class, 'tolak'])->name('tolak.kirim');
    });
});