<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Console;

// use Illuminate\Filesystem\Filesystem;
// use Symfony\Component\Process\Process;
use Illuminate\Console\Command;

class InitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-responder:init
                            {has_oauth_error_method=true : Flag for creating class with OAuthError() method}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install package features';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->argument('has_oauth_error_method')) {
            copy(__DIR__ . '/../../stubs/ApiBaseResponder.stub', __DIR__ . '/../../src/ApiBaseResponder.php');
        }
    }
}
