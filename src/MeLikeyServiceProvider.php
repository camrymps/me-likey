<?php

namespace Camrymps\MeLikey;

use Illuminate\Support\ServiceProvider;

class MeLikeyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(
            \dirname(__DIR__) . '/config/me-likey.php', 'me-likey'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            \dirname(__DIR__) . '/config/me-likey.php' => config_path('me-likey.php'),
        ], 'config');

        // Publish migration(s)
        $this->publishes([
            \dirname(__DIR__) . '/migrations' => database_path('migrations'),
        ], 'migrations');

        // Load migration(s)
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(\dirname(__DIR__) . '/migrations');
        }
    }
}
