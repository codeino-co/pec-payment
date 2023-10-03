<?php

namespace Pec;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__.'/../config/pec.php' => config_path('pec.php')], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/pec.php', 'pec');
    }
}