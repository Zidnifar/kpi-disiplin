<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pembatas akses berdasarkan peran. Contoh pemakaian di routes/web.php:
 *
 *   Route::middleware(['auth', 'role:kepala_unit'])->group(...);
 *   Route::middleware(['auth', 'role:kepala_produksi,operasional_manager,general_manager'])->group(...);
 *
 * Pemeriksaan ini harus selalu di sisi server, bukan hanya menyembunyikan menu.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $pengguna = $request->user();

        if (! $pengguna || ! $pengguna->aktif() || ! $pengguna->punyaRole(...$roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
