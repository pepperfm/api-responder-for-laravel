<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Pepperfm\ApiBaseResponder\ApiBaseResponder;
use Pepperfm\ApiBaseResponder\Console\InitCommand;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;
use Pepperfm\ApiBaseResponder\Http\Middleware\ForceJsonResponse;

/**
 * @property \Illuminate\Foundation\Application $app
 */
class ApiBaseResponderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningUnitTests()) {
            $this->loadRoutesFrom(__DIR__ . '/../../tests/Fixtures/api.php');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/config.php' => config_path('laravel-api-responder.php'),
            ], 'config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'laravel-api-responder');

        $this->app->singleton(ResponseContract::class, ApiBaseResponder::class);

        if (config('laravel-api-responder.force_json_response_header', true)) {
            $this->app->make(Kernel::class)->prependMiddlewareToGroup(
                group: 'api',
                middleware: ForceJsonResponse::class
            );
        }
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InitCommand::class,
            ]);
        }
    }
}
