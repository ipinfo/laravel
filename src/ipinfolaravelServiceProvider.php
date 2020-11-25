<?php

namespace ipinfo\ipinfolaravel;

use Illuminate\Support\ServiceProvider;

class ipinfolaravelServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     * @return void
     */
    public function boot()
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ipinfolaravel.php',
            'ipinfolaravel'
        );

        // Register the service the package provides.
        $this->app->singleton('ipinfolaravel', function ($app) {
            return new ipinfolaravel;
        });
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ['ipinfolaravel'];
    }

    /**
     * Console-specific booting.
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/ipinfolaravel.php' => config_path('ipinfolaravel.php'),
        ], 'ipinfolaravel.config');
    }
}
