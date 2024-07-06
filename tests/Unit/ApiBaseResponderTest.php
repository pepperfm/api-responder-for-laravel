<?php

use Mockery\MockInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

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
    'entities collection' => collect([range(1, 10), range(2, 5), range(4, 9), 'key' => [1, 'value']])
]);
