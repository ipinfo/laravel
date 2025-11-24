<?php

namespace ipinfo\ipinfolaravel\plus;

use Illuminate\Support\ServiceProvider;

class ipinfopluslaravelServiceProvider extends ServiceProvider
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
            __DIR__ . "/../../config/ipinfopluslaravel.php",
            "ipinfopluslaravel",
        );

        // Register the service the package provides.
        $this->app->singleton(
            "ipinfopluslaravel",
            fn($app) => new ipinfopluslaravel(),
        );
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ["ipinfopluslaravel"];
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
                __DIR__ . "/../../config/ipinfopluslaravel.php" => config_path(
                    "ipinfopluslaravel.php",
                ),
            ],
            "ipinfopluslaravel.config",
        );
    }
}
