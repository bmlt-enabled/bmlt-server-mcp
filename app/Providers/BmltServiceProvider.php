<?php

namespace App\Providers;

use App\Services\Bmlt\BmltClientFactory;
use App\Services\Geocoding\GeocoderManager;
use Illuminate\Support\ServiceProvider;

class BmltServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/bmlt.php', 'bmlt');

        $this->app->singleton(BmltClientFactory::class);
        $this->app->singleton(GeocoderManager::class);
    }
}
