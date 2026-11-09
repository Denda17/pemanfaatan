<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Prevent 500 crashes from touch(): Utime failed on compiled Blade views when ownership differs between CLI and PHP-FPM
        set_error_handler(function (int $errno, string $errstr, ?string $errfile = null) {
            if (str_contains($errstr, 'touch(): Utime failed') || (str_contains($errstr, 'Utime failed') && str_contains($errfile ?? '', 'BladeCompiler'))) {
                return true;
            }

            return false;
        }, E_WARNING);
    }
}
