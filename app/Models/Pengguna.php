<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Akun login. Peran (role) menentukan hak akses:
 * admin | karyawan | kepala_unit | kepala_produksi | operasional_manager | general_manager
 */
class Pengguna extends Authenticatable
{
    use HasUuids;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_KARYAWAN = 'karyawan';
    public const ROLE_KEPALA_UNIT = 'kepala_unit';
    public const ROLE_KEPALA_PRODUKSI = 'kepala_produksi';
    public const ROLE_OPERASIONAL_MANAGER = 'operasional_manager';
    public const ROLE_GENERAL_MANAGER = 'general_manager';

    protected $table = 'pengguna';
    protected $primaryKey = 'id_pengguna';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $hidden = ['password'];

    protected $casts = [
        'password' => 'hashed',
        'dibuat_pada' => 'datetime',
    ];

    /** Tabel tidak punya kolom remember_token. */
    public function getRememberTokenName()
    {
        return '';
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    public function namaPeran(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_KARYAWAN => 'Karyawan',
            self::ROLE_KEPALA_UNIT => 'Kepala Unit',
            self::ROLE_KEPALA_PRODUKSI => 'Kepala Produksi',
            self::ROLE_OPERASIONAL_MANAGER => 'Operasional Manager',
            self::ROLE_GENERAL_MANAGER => 'General Manager',
            default => (string) $this->role,
        };
    }

    /** Nama yang ditampilkan: nama karyawan, atau username untuk akun admin. */
    public function namaTampil(): string
    {
        return $this->karyawan?->nama_karyawan ?? $this->username;
    }

    public function punyaRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function aktif(): bool
    {
        return $this->status_akun === 'Aktif';
    }
}
