<?php

declare(strict_types=1);

use Mockery\MockInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;
use Pepperfm\ApiBaseResponder\Tests\Unit\ExampleController;

use function Pest\Laravel\instance;

test('response format', function ($responseData) {
    instance(
        ResponseContract::class,
        Mockery::mock(ResponseContract::class, function (MockInterface $mock) use ($responseData) {
            $mock->shouldReceive('response')
                ->once()
                ->andReturn(
                    new JsonResponse([
                        'entities' => $responseData,
                        'meta' => [],
                    ])
                );
        })
    );

    $url = 'https://dummy.com/api/users';

    Http::fake([
        $url => Http::response(
            $this->app->make(ResponseContract::class)->response([])->getContent()
        ),
    ]);

    $response = Http::get($url)->json();

    expect($response)->toHaveKeys(['entities', 'meta']);
})->with([
    'single entity object' => (object) ['key' => 'value', 'nested' => range(1, 4), ...range(1, 10)],
    'entities collection' => collect([range(1, 10), range(2, 5), range(4, 9), 'key' => [1, 'value']]),
]);

test('flexible data keys', function (string $method, string $expectedKey, bool $configState) {
    config()->set('laravel-api-responder.using_for_rest', $configState);

    /** @var ExampleController $controller */
    $controller = $this->app->make(ExampleController::class);

    /** @var JsonResponse $response */
    $response = $controller->$method()->getData(true);

    expect($response)->toHaveKeys([$expectedKey, 'meta', 'message']);
})->with([
    'ON config state: plural for index' => ['index', 'entities', true],
    'ON config state: plural for random method name' => ['omg', 'entities', true],
    'ON config state: singular for show' => ['show', 'entity', true],
    'ON config state: singular for edit' => ['edit', 'entity', true],

    'OFF config state: plural for index' => ['index', 'entities', false],
    'OFF config state: plural for random method name' => ['omg', 'entities', false],
    'OFF config state: singular for show' => ['show', 'entities', false],
    'OFF config state: singular for edit' => ['edit', 'entities', false],
]);
