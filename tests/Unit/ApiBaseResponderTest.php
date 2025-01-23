<?php

declare(strict_types=1);

use Mockery\MockInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;
use Pepperfm\ApiBaseResponder\Tests\Fixtures\ExampleController;

use function Pest\Laravel\{instance, getJson};

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

test('flexible data keys', function (string $method, string $expectedKey, bool $configState = true) {
    config()->set('laravel-api-responder.using_for_rest', $configState);

    /** @var ExampleController $controller */
    $controller = $this->app->make(ExampleController::class);

    /** @var JsonResponse $response */
    $response = $controller->$method()->getData(true);

    expect($response)->toHaveKeys([$expectedKey, 'meta', 'message']);
})->with([
    'ON config state: plural for index' => ['index', 'entities'],
    'ON config state: plural for random method name' => ['omg', 'entities'],
    'ON config state: singular for show' => ['show', 'entity'],
    'ON config state: singular for edit' => ['update', 'entity'],

    'OFF config state: plural for index' => ['index', 'entities', false],
    'OFF config state: plural for random method name' => ['omg', 'entities', false],
    'OFF config state: singular for show' => ['show', 'entities', false],
    'OFF config state: singular for edit' => ['update', 'entities', false],

    'attribute without parameter' => ['attributeWithoutParam', 'entity'],
    'attribute without parameter and config ON' => ['attributeWithoutParam', 'entity'],
    'attribute without parameter and config OFF' => ['attributeWithoutParam', 'entity', false],
    'attribute with parameter' => ['attributeWithParam', 'random_key'],
    'attribute with parameter and config ON' => ['attributeWithParam', 'random_key'],
    'attribute with parameter and config OFF' => ['attributeWithParam', 'random_key', false],
]);

test('force json headers', function () {
    $response = getJson(route('api.users.index'));

    expect($response->headers->get('content-type'))->toBe('application/json; charset=UTF-8');
});

test('without wrapping', function () {
    config()->set('laravel-api-responder.without_wrapping', true);

    /** @var ExampleController $controller */
    $controller = $this->app->make(ExampleController::class);

    /** @var JsonResponse $response */
    $response = $controller->withoutWrapping()->getData(true);

    expect($response)->toHaveKeys([
        'id',
        'name',
        'email',
        'meta',
        'message',
    ]);
});