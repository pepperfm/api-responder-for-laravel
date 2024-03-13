<?php

use Mockery\MockInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

use function Pest\Laravel\instance;

test('response format', function () {
    instance(
        ResponseContract::class,
        Mockery::mock(ResponseContract::class, function (MockInterface $mock) {
            $mock->shouldReceive('response')
                ->once()
                ->andReturn(
                    new JsonResponse([
                        'entities' => [],
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
});
