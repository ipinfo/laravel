<?php

namespace ipinfo\ipinfolaravel\core;

use Illuminate\Support\ServiceProvider;

class ipinfocorelaravelServiceProvider extends ServiceProvider
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
            __DIR__ . "/../../config/ipinfocorelaravel.php",
            "ipinfocorelaravel",
        );

        // Register the service the package provides.
        $this->app->singleton(
            "ipinfocorelaravel",
            fn($app) => new ipinfocorelaravel(),
        );
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ["ipinfocorelaravel"];
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
                __DIR__ . "/../../config/ipinfocorelaravel.php" => config_path(
                    "ipinfocorelaravel.php",
                ),
            ],
            "ipinfocorelaravel.config",
        );
    }
}
