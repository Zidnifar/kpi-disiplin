<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahan untuk tahap 3-5:
 *  - shift_kerja.status_shift : shift bisa dinonaktifkan (Aktif | Nonaktif)
 *  - rencana_lembur.id_shift  : shift yang dipilih pada formulir pengajuan lembur (opsional, tanpa FK agar aman di SQLite)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_kerja', function (Blueprint $t) {
            $t->string('status_shift', 20)->default('Aktif');
        });
        Schema::table('rencana_lembur', function (Blueprint $t) {
            $t->char('id_shift', 36)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shift_kerja', function (Blueprint $t) {
            $t->dropColumn('status_shift');
        });
        Schema::table('rencana_lembur', function (Blueprint $t) {
            $t->dropColumn('id_shift');
        });
    }
};
