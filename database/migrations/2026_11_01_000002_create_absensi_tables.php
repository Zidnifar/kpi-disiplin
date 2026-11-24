<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_kerja', function (Blueprint $t) {
            $t->char('id_shift', 36)->primary();
            $t->string('kode_shift', 10)->unique();
            $t->string('nama_shift', 50);
            $t->time('jam_mulai');
            $t->time('jam_selesai');
            $t->unsignedInteger('toleransi_menit')->default(0);          // 0 = terlambat jika lewat jam mulai
            $t->unsignedInteger('batas_pulang_awal_menit')->default(60); // pulang >= 60 menit lebih awal
        });

        Schema::create('jadwal_shift', function (Blueprint $t) {
            $t->char('id_jadwal', 36)->primary();
            $t->char('id_karyawan', 36);
            $t->char('id_shift', 36);
            $t->date('tanggal_jadwal');

            $t->unique(['id_karyawan', 'tanggal_jadwal']);
            $t->foreign('id_karyawan')->references('id_karyawan')->on('karyawan');
            $t->foreign('id_shift')->references('id_shift')->on('shift_kerja');
        });

        Schema::create('impor_absensi', function (Blueprint $t) {
            $t->char('id_impor', 36)->primary();
            $t->char('id_pengguna', 36);
            $t->string('nama_file', 100);
            $t->char('periode_impor', 7); // YYYY-MM
            $t->dateTime('tanggal_impor')->useCurrent();
            $t->unsignedInteger('jumlah_baris')->default(0);
            $t->string('status_impor', 30);

            $t->foreign('id_pengguna')->references('id_pengguna')->on('pengguna');
        });

        Schema::create('absensi', function (Blueprint $t) {
            $t->char('id_absensi', 36)->primary();
            $t->char('id_karyawan', 36);
            $t->char('id_jadwal', 36)->nullable();
            $t->char('id_impor', 36);
            $t->date('tanggal_absen');
            $t->time('jam_masuk')->nullable();
            $t->time('jam_pulang')->nullable();
            $t->string('status_kehadiran', 50);
            $t->text('keterangan_absen')->nullable();

            $t->unique(['id_karyawan', 'tanggal_absen']); // satu rekap per karyawan per hari
            $t->foreign('id_karyawan')->references('id_karyawan')->on('karyawan');
            $t->foreign('id_jadwal')->references('id_jadwal')->on('jadwal_shift')->nullOnDelete();
            $t->foreign('id_impor')->references('id_impor')->on('impor_absensi');
        });

        Schema::create('kesalahan_absensi', function (Blueprint $t) {
            $t->char('id_kesalahan', 36)->primary();
            $t->char('id_absensi', 36);
            $t->char('id_pengguna', 36)->nullable(); // admin yang memperbaiki
            $t->string('jenis_kesalahan', 50);
            $t->string('status_perbaikan', 30)->default('Belum diperbaiki');
            $t->text('catatan')->nullable();
            $t->dateTime('tanggal_perbaikan')->nullable();

            $t->foreign('id_absensi')->references('id_absensi')->on('absensi')->cascadeOnDelete();
            $t->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });

        Schema::create('konfirmasi_absensi', function (Blueprint $t) {
            $t->char('id_konfir', 36)->primary();
            $t->char('id_absensi', 36)->unique(); // satu konfirmasi aktif per absensi
            $t->char('id_pemverifikasi', 36)->nullable();
            $t->dateTime('tanggal_konfir');
            $t->string('jawaban_konfir', 30); // Benar | Koreksi
            $t->time('jam_masuk_koreksi')->nullable();
            $t->time('jam_pulang_koreksi')->nullable();
            $t->text('alasan')->nullable();
            $t->string('status_konfir', 50);  // Menunggu verifikasi | Terverifikasi | Ditolak
            $t->dateTime('tanggal_verifikasi')->nullable();
            $t->text('catatan_verifikasi')->nullable();

            $t->foreign('id_absensi')->references('id_absensi')->on('absensi')->cascadeOnDelete();
            $t->foreign('id_pemverifikasi')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (['konfirmasi_absensi', 'kesalahan_absensi', 'absensi', 'impor_absensi', 'jadwal_shift', 'shift_kerja'] as $tabel) {
            Schema::dropIfExists($tabel);
        }
        Schema::enableForeignKeyConstraints();
    }
};
