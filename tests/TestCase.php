<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Pepperfm\ApiBaseResponder\Providers\ApiBaseResponderServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();
    }

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
