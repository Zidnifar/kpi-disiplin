<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_penilaian', function (Blueprint $t) {
            $t->char('id_periode', 36)->primary();
            $t->string('nama_periode', 50);
            $t->unsignedSmallInteger('tahun');
            $t->unsignedTinyInteger('triwulan'); // 1..4 ; semester = CEIL(triwulan / 2)
            $t->date('tanggal_mulai');
            $t->date('tanggal_selesai');
            $t->string('status_periode', 50)->default('Berjalan');

            $t->unique(['tahun', 'triwulan']);
        });
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE periode_penilaian ADD CONSTRAINT ck_periode_tw CHECK (triwulan BETWEEN 1 AND 4)');
        }

        Schema::create('indikator_kpi', function (Blueprint $t) {
            $t->char('id_indikator', 36)->primary();
            $t->string('kode_indikator', 10)->unique();
            $t->string('nama_indikator', 100);
            $t->text('deskripsi')->nullable();
            $t->unsignedInteger('bobot_poin'); // poin pengurang per kejadian
            $t->string('status_indikator', 20)->default('Aktif');
        });

        Schema::create('predikat_nilai', function (Blueprint $t) {
            $t->char('id_predikat', 36)->primary();
            $t->string('nama_predikat', 30);
            $t->decimal('nilai_min', 5, 2);
            $t->decimal('nilai_max', 5, 2);
            $t->string('status_predikat', 20)->default('Aktif');
        });

        Schema::create('pelanggaran_karyawan', function (Blueprint $t) {
            $t->char('id_pelanggaran', 36)->primary();
            $t->char('id_karyawan', 36);
            $t->char('id_indikator', 36);
            $t->char('id_absensi', 36)->nullable();
            $t->char('id_pencatat', 36)->nullable();
            $t->date('tanggal_pelanggaran');
            $t->string('sumber', 20); // Absensi | K3 | Manual
            $t->text('keterangan')->nullable();

            $t->index(['id_karyawan', 'tanggal_pelanggaran']);
            $t->foreign('id_karyawan')->references('id_karyawan')->on('karyawan');
            $t->foreign('id_indikator')->references('id_indikator')->on('indikator_kpi');
            $t->foreign('id_absensi')->references('id_absensi')->on('absensi')->nullOnDelete();
            $t->foreign('id_pencatat')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });

        Schema::create('hasil_kpi', function (Blueprint $t) {
            $t->char('id_hasil', 36)->primary();
            $t->char('id_karyawan', 36);
            $t->char('id_periode', 36);
            $t->char('id_predikat', 36)->nullable();
            $t->char('id_pemeriksa', 36)->nullable();   // kepala unit
            $t->char('id_pemvalidasi', 36)->nullable(); // admin
            $t->unsignedInteger('nilai_awal')->default(100);
            $t->unsignedInteger('total_pengurang')->default(0);
            $t->decimal('nilai_triwulan', 5, 2);
            $t->string('status_periksa', 30)->default('Belum diperiksa');
            $t->string('status_validasi', 30)->default('Menunggu validasi');
            $t->dateTime('tanggal_penilaian');
            $t->dateTime('tanggal_periksa')->nullable();
            $t->dateTime('tanggal_validasi')->nullable();
            $t->text('catatan_periksa')->nullable();
            $t->text('catatan_validasi')->nullable();

            $t->unique(['id_karyawan', 'id_periode']);
            $t->foreign('id_karyawan')->references('id_karyawan')->on('karyawan');
            $t->foreign('id_periode')->references('id_periode')->on('periode_penilaian');
            $t->foreign('id_predikat')->references('id_predikat')->on('predikat_nilai')->nullOnDelete();
            $t->foreign('id_pemeriksa')->references('id_pengguna')->on('pengguna')->nullOnDelete();
            $t->foreign('id_pemvalidasi')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });

        Schema::create('detail_penilaian_kpi', function (Blueprint $t) {
            $t->char('id_detail', 36)->primary();
            $t->char('id_hasil', 36);
            $t->char('id_indikator', 36);
            $t->unsignedInteger('frekuensi');
            $t->unsignedInteger('bobot_poin');      // salinan bobot saat dihitung
            $t->unsignedInteger('total_pengurang'); // frekuensi x bobot_poin

            $t->unique(['id_hasil', 'id_indikator']);
            $t->foreign('id_hasil')->references('id_hasil')->on('hasil_kpi')->cascadeOnDelete();
            $t->foreign('id_indikator')->references('id_indikator')->on('indikator_kpi');
        });

        Schema::create('nilai_semester', function (Blueprint $t) {
            $t->char('id_nilai_semester', 36)->primary();
            $t->char('id_karyawan', 36);
            $t->char('id_predikat', 36)->nullable();
            $t->char('id_hasil_tw_pertama', 36); // TW1 (smt 1) / TW3 (smt 2)
            $t->char('id_hasil_tw_kedua', 36);   // TW2 (smt 1) / TW4 (smt 2)
            $t->unsignedSmallInteger('tahun');
            $t->unsignedTinyInteger('semester');
            $t->decimal('nilai_semester', 5, 2);
            $t->dateTime('tanggal_hitung');

            $t->unique(['id_karyawan', 'tahun', 'semester']);
            $t->foreign('id_karyawan')->references('id_karyawan')->on('karyawan');
            $t->foreign('id_predikat')->references('id_predikat')->on('predikat_nilai')->nullOnDelete();
            $t->foreign('id_hasil_tw_pertama')->references('id_hasil')->on('hasil_kpi');
            $t->foreign('id_hasil_tw_kedua')->references('id_hasil')->on('hasil_kpi');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (['nilai_semester', 'detail_penilaian_kpi', 'hasil_kpi', 'pelanggaran_karyawan', 'predikat_nilai', 'indikator_kpi', 'periode_penilaian'] as $tabel) {
            Schema::dropIfExists($tabel);
        }
        Schema::enableForeignKeyConstraints();
    }
};
