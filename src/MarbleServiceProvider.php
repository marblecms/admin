<?php

namespace Marble\Admin;

use Illuminate\Support\ServiceProvider;

class MarbleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__.'/app/Http/routes.php';

        $this->publishes([
            __DIR__.'/public/assets/' => public_path('vendor/admin'),
        ], 'public');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'admin');

        foreach (glob(app_path().'/Helpers/*.php') as $filename) {
            require_once $filename;
        }
    }
}
