<?php

declare(strict_types=1);

namespace Tests;

use Pepperfm\ApiBaseResponder\Providers\ApiBaseResponderServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @inheritdoc
     */
    protected function getPackageProviders($app): array
    {
        return [
            ApiBaseResponderServiceProvider::class,
        ];
    }
}
