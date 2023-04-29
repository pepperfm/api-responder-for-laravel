<?php

namespace Pepperfm\ApiBaseResponder\Providers;

use Illuminate\Support\ServiceProvider;
use Pepperfm\ApiBaseResponder\ApiBaseResponder;
use Pepperfm\ApiBaseResponder\Console\InitCommand;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

class ApiBaseResponderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'laravel-api-responder');
        // $this->loadViewsFrom(__DIR__.'/../../resources/views', 'laravel-api-responder');
        // $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/../routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/config.php' => config_path('laravel-api-responder.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../../resources/views' => resource_path('views/vendor/laravel-api-responder'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../../resources/assets' => public_path('vendor/laravel-api-responder'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../../resources/lang' => resource_path('lang/vendor/laravel-api-responder'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'laravel-api-responder');

        // Register the main class to use with the facade
        $this->app->singleton(ResponseContract::class, ApiBaseResponder::class);

        $this->registerCommands();
    }

    /**
     * Register the Invoices Artisan commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InitCommand::class,
            ]);
        }
    }
}
