<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Tests;
// namespace Tests;

use Pepperfm\ApiBaseResponder\ApiBaseResponderServiceProvider;

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
