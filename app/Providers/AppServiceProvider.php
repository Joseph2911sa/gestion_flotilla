<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Forzar timezone de Costa Rica en toda la aplicación
        Carbon::setLocale('es');
        date_default_timezone_set('America/Costa_Rica');
    }
}