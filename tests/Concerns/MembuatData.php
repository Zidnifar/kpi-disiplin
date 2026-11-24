<?php

namespace Tests\Concerns;

use App\Models\Absensi;
use App\Models\Departemen;
use App\Models\ImporAbsensi;
use App\Models\Karyawan;
use App\Models\KesalahanAbsensi;
use App\Models\Pengguna;
use App\Models\ShiftKerja;
use Illuminate\Support\Str;

/** Pembuat data uji sederhana (tanpa factory) untuk tes fitur tahap 3-5. */
trait MembuatData
{
    protected function dept(string $nama = 'Formalin'): Departemen
    {
        return Departemen::firstOrCreate(['nama_dept' => $nama]);
    }

    protected function karyawan(Departemen $dept, string $nama = 'Andi Pratama', array $ubah = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'nip' => strtoupper(Str::random(8)),
            'nama_karyawan' => $nama,
            'jabatan' => 'Operator',
            'id_dept' => $dept->id_dept,
            'status_karyawan' => 'Aktif',
        ], $ubah));
    }

    protected function akun(string $role, ?Karyawan $karyawan = null, ?string $username = null): Pengguna
    {
        return Pengguna::create([
            'id_karyawan' => $karyawan?->id_karyawan,
            'username' => $username ?? $role . '_' . strtolower(Str::random(5)),
            'password' => 'Rahasia123',
            'role' => $role,
            'status_akun' => 'Aktif',
        ]);
    }

    protected function shiftPagi(): ShiftKerja
    {
        return ShiftKerja::firstOrCreate(['kode_shift' => 'P'], [
            'nama_shift' => 'Pagi',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '17:00:00',
            'toleransi_menit' => 0,
            'batas_pulang_awal_menit' => 60,
        ]);
    }

    protected function impor(Pengguna $admin): ImporAbsensi
    {
        return ImporAbsensi::create([
            'id_pengguna' => $admin->id_pengguna,
            'nama_file' => 'uji.xlsx',
            'periode_impor' => '2026-10',
            'status_impor' => 'Selesai',
        ]);
    }

    protected function absensi(Karyawan $k, ImporAbsensi $impor, string $tanggal, ?string $masuk, ?string $pulang, string $status): Absensi
    {
        return Absensi::create([
            'id_karyawan' => $k->id_karyawan,
            'id_impor' => $impor->id_impor,
            'tanggal_absen' => $tanggal,
            'jam_masuk' => $masuk,
            'jam_pulang' => $pulang,
            'status_kehadiran' => $status,
        ]);
    }

    protected function kesalahan(Absensi $a, string $jenis = 'Jam pulang kosong'): KesalahanAbsensi
    {
        return KesalahanAbsensi::create(['id_absensi' => $a->id_absensi, 'jenis_kesalahan' => $jenis, 'status_perbaikan' => 'Belum diperbaiki']);
    }
}
