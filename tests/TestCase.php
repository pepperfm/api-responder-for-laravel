<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Tests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\WithFaker;
use Pepperfm\ApiBaseResponder\Providers\ApiBaseResponderServiceProvider;
use Pepperfm\ApiBaseResponder\Tests\Fixtures\ExampleController;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();
    }

    protected function getPackageProviders($app): array
    {
        return [ApiBaseResponderServiceProvider::class];
    }

    protected function defineRoutes($router): void
    {
        $router->get('api/users', [ExampleController::class, 'index'])->name('api.users.index');
        $router->get('api/accept', fn(Request $request) => response()->json([
            'accept' => $request->header('Accept'),
        ]))->name('api.accept');
    }
}
