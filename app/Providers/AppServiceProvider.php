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
        require_once app_path('Support/site-helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(fn () => app(\Saola\Core\Engines\ViewContextManager::class)->registerContext('web', [
            'base' => 'web.themes.default.views',
        ]));
        //
    }
}
