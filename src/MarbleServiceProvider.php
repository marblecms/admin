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
        

        $this->mergeConfigFrom(
            __DIR__.'/config/app.php', 'app'
        );
        
        $this->loadTranslationsFrom(__DIR__.'/resources/lang/', 'admin');

        $this->loadViewsFrom(__DIR__.'/resources/views', 'admin');
        
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        foreach (glob(app_path().'/Helpers/*.php') as $filename) {
            require_once $filename;
        }
    }
}
