<?php

namespace App\Providers;

use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Login memakai tabel `pengguna`, bukan tabel `users` bawaan Laravel.
        config(['auth.providers.users.model' => Pengguna::class]);

        // Nama bulan/hari berbahasa Indonesia (translatedFormat) dan tampilan paginasi sendiri (tanpa Tailwind).
        Carbon::setLocale('id');
        Paginator::defaultView('partials.paginasi');
    }
}
