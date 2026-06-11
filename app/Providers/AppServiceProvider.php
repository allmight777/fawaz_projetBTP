<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Forcer HTTPS en production
        if (app()->environment('production')) {
            URL::forceScheme('https');

            // Optionnel : Forcer aussi pour les assets
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }
}
