<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departemen', function (Blueprint $t) {
            $t->char('id_dept', 36)->primary();
            $t->string('nama_dept', 100);
            $t->char('id_kepala_unit', 36)->nullable()->unique(); // satu karyawan memimpin satu departemen
        });

        Schema::create('karyawan', function (Blueprint $t) {
            $t->char('id_karyawan', 36)->primary();
            $t->char('id_dept', 36);
            $t->string('nip', 20)->nullable()->unique(); // NIP tidak wajib
            $t->string('nama_karyawan', 100);
            $t->string('jabatan', 100);
            $t->string('status_karyawan', 20)->default('Aktif');
            $t->string('no_hp', 15)->nullable();
            $t->string('email', 100)->nullable();
            $t->char('jenis_kelamin', 1)->nullable();

            $t->foreign('id_dept')->references('id_dept')->on('departemen');
        });

        // relasi melingkar: departemen.id_kepala_unit -> karyawan
        // (SQLite untuk tes tidak bisa menambah FK pada tabel yang sudah ada, jadi dilewati di sana)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('departemen', function (Blueprint $t) {
                $t->foreign('id_kepala_unit')->references('id_karyawan')->on('karyawan')->nullOnDelete();
            });
        }

        // role: admin | karyawan | kepala_unit | kepala_produksi | operasional_manager | general_manager
        Schema::create('pengguna', function (Blueprint $t) {
            $t->char('id_pengguna', 36)->primary();
            $t->char('id_karyawan', 36)->nullable()->unique(); // kosong untuk akun admin
            $t->string('username', 100)->unique();
            $t->string('password', 255);
            $t->string('role', 30);
            $t->string('status_akun', 20)->default('Aktif');
            $t->dateTime('dibuat_pada')->useCurrent();

            $t->foreign('id_karyawan')->references('id_karyawan')->on('karyawan');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pengguna');
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('departemen', function (Blueprint $t) {
                $t->dropForeign(['id_kepala_unit']);
            });
        }
        Schema::dropIfExists('karyawan');
        Schema::dropIfExists('departemen');
        Schema::enableForeignKeyConstraints();
    }
};
