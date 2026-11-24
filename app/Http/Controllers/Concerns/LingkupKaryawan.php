<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Karyawan;

/** Mengambil data karyawan milik akun yang sedang masuk (akun admin tidak punya). */
trait LingkupKaryawan
{
    protected function karyawanSaya(): Karyawan
    {
        $karyawan = request()->user()?->karyawan;
        abort_if($karyawan === null, 403, 'Akun ini tidak terhubung dengan data karyawan.');

        return $karyawan;
    }
}
