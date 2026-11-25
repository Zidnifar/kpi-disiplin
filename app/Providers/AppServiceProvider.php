<?php

namespace App\Providers;

use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        config(['auth.providers.users.model' => Pengguna::class]);

        Carbon::setLocale('id');
        Paginator::defaultView('partials.paginasi');

    if (app()->environment('production')) {
        URL::forceScheme('https');
        }
    }
}
