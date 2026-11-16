<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Providers;

use Illuminate\Support\ServiceProvider;
use Pepperfm\ApiBaseResponder\ApiBaseResponder;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

class ApiBaseResponderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'laravel-api-responder');

        $this->app->singleton(ResponseContract::class, ApiBaseResponder::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/config.php' => config_path('laravel-api-responder.php'),
            ], 'config');
        }
    }
}
