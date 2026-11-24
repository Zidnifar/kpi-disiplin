<?php

namespace App\Support;

use App\Models\Pengguna;
use Illuminate\Support\Facades\Route;

final class Menu
{
    /**
     * Menu sidebar untuk pengguna. Setiap item diberi 'url' (null bila rutenya belum ada) dan 'aktif'.
     * Item dengan 'anak' adalah dropdown: 'aktif' dan 'buka' bernilai true bila salah satu anaknya aktif.
     *
     * @return array<int,array{judul:string,item:array<int,array<string,mixed>>}>
     */
    public static function untuk(Pengguna $pengguna): array
    {
        $hasil = [];

        foreach (config('menu.' . $pengguna->role, []) as $grup) {
            $item = [];
            foreach ($grup['item'] as $m) {
                $item[] = isset($m['anak']) ? self::induk($m) : self::tunggal($m);
            }
            $hasil[] = ['judul' => $grup['judul'], 'item' => $item];
        }

        return $hasil;
    }

    /** @return array<string,mixed> */
    private static function tunggal(array $m): array
    {
        $ada = Route::has($m['rute']);

        return $m + ['url' => $ada ? route($m['rute']) : null, 'aktif' => $ada && self::cocok($m)];
    }

    /** @return array<string,mixed> */
    private static function induk(array $m): array
    {
        $anak = array_map(fn (array $a) => self::tunggal($a), $m['anak']);
        $aktif = count(array_filter($anak, fn (array $a) => $a['aktif'])) > 0;

        return ['anak' => $anak, 'url' => null, 'aktif' => $aktif, 'buka' => $aktif] + $m;
    }

    /** Apakah halaman yang sedang dibuka cocok dengan rute/pola menu ini. */
    private static function cocok(array $m): bool
    {
        $pola = (array) ($m['pola'] ?? []);
        if (! ($m['hanya_pola'] ?? false)) {
            $pola[] = $m['rute'];
        }

        return $pola !== [] && request()->routeIs(...$pola);
    }
}