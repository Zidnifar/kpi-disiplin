<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rencana_lembur', function (Blueprint $t) {
            $t->char('id_rencana', 36)->primary();
            $t->char('id_dept', 36);
            $t->char('id_pengaju', 36); // kepala unit (karyawan)
            $t->string('no_pengajuan', 30)->unique();
            $t->date('tanggal_lembur');
            $t->time('jam_mulai');
            $t->time('jam_selesai');
            $t->text('uraian_pekerjaan');
            $t->text('alasan_lembur');
            // Draf | Menunggu Ka. Produksi | Menunggu General Manager | Disetujui | Ditolak
            $t->string('status_rencana', 40)->default('Draf');
            $t->dateTime('tanggal_pengajuan')->nullable();

            $t->foreign('id_dept')->references('id_dept')->on('departemen');
            $t->foreign('id_pengaju')->references('id_karyawan')->on('karyawan');
        });

        Schema::create('detail_lembur', function (Blueprint $t) {
            $t->char('id_detail_lembur', 36)->primary();
            $t->char('id_rencana', 36);
            $t->char('id_karyawan', 36);
            $t->decimal('jam_rencana', 4, 1);
            $t->string('pekerjaan', 100)->nullable();
            // realisasi (diisi kepala unit setelah lembur)
            $t->char('id_pengisi', 36)->nullable();
            $t->time('jam_mulai_real')->nullable();
            $t->time('jam_selesai_real')->nullable();
            $t->decimal('total_jam', 4, 1)->nullable();
            $t->text('catatan_realisasi')->nullable();
            $t->dateTime('tanggal_isi')->nullable();

            $t->unique(['id_rencana', 'id_karyawan']);
            $t->foreign('id_rencana')->references('id_rencana')->on('rencana_lembur')->cascadeOnDelete();
            $t->foreign('id_karyawan')->references('id_karyawan')->on('karyawan');
            $t->foreign('id_pengisi')->references('id_karyawan')->on('karyawan')->nullOnDelete();
        });

        Schema::create('persetujuan_lembur', function (Blueprint $t) {
            $t->char('id_persetujuan', 36)->primary();
            $t->char('id_rencana', 36);
            $t->char('id_pengguna', 36);
            $t->string('tahap', 20);              // periksa | ketahui | putuskan
            $t->string('status_persetujuan', 20); // Diperiksa | Dikembalikan | Diketahui | Disetujui | Ditolak
            $t->text('catatan')->nullable();      // wajib diisi saat Ditolak
            $t->dateTime('tanggal_proses');

            $t->unique(['id_rencana', 'tahap']);
            $t->foreign('id_rencana')->references('id_rencana')->on('rencana_lembur')->cascadeOnDelete();
            $t->foreign('id_pengguna')->references('id_pengguna')->on('pengguna');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (['persetujuan_lembur', 'detail_lembur', 'rencana_lembur'] as $tabel) {
            Schema::dropIfExists($tabel);
        }
        Schema::enableForeignKeyConstraints();
    }
};
