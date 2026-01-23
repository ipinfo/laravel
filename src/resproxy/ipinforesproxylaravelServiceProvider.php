<?php

namespace ipinfo\ipinfolaravel\resproxy;

use Illuminate\Support\ServiceProvider;

class ipinforesproxylaravelServiceProvider extends ServiceProvider
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
            __DIR__ . "/../../config/ipinforesproxylaravel.php",
            "ipinforesproxylaravel",
        );

        // Register the service the package provides.
        $this->app->singleton(
            "ipinforesproxylaravel",
            fn($app) => new ipinforesproxylaravel(),
        );
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ["ipinforesproxylaravel"];
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
                __DIR__ . "/../../config/ipinforesproxylaravel.php" => config_path(
                    "ipinforesproxylaravel.php",
                ),
            ],
            "ipinforesproxylaravel.config",
        );
    }
}
