<?php

namespace ipinfo\ipinfolaravel\lite;

use Illuminate\Support\ServiceProvider;

class ipinfolitelaravelServiceProvider extends ServiceProvider
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
            __DIR__ . "/../../config/ipinfolitelaravel.php",
            "ipinfolitelaravel",
        );

        // Register the service the package provides.
        $this->app->singleton(
            "ipinfolitelaravel",
            fn($app) => new ipinfolitelaravel(),
        );
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ["ipinfolitelaravel"];
    }

    /**
     * Console-specific booting.
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes(
            [
                __DIR__ . "/../../config/ipinfolitelaravel.php" => config_path(
                    "ipinfolitelaravel.php",
                ),
            ],
            "ipinfolitelaravel.config",
        );
    }
}
