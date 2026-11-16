<?php

declare(strict_types=1);

use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Pepperfm\ApiResponder\JsonResponder;

test('resolves via Laravel dependency injection without a service provider', function () {
    expect(app(JsonResponder::class))->toBeInstanceOf(JsonResponder::class);
});

test('formats a success response using a stable data key', function () {
    $response = app(JsonResponder::class)->response(
        [['id' => 1]],
        ['source' => 'api'],
        'All good',
    );

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getData(true))->toBe([
            'data' => [['id' => 1]],
            'meta' => ['source' => 'api'],
            'message' => 'All good',
        ]);
});

test('supports Laravel collections and preserves Unicode', function () {
    $response = app(JsonResponder::class)->response(collect([
        ['name' => 'Привет'],
    ]));

    expect($response->getData(true)['data'])->toBe([['name' => 'Привет']])
        ->and($response->getContent())->toContain('Привет');
});

test('allows an explicit HTTP status for an ordinary response', function () {
    $response = app(JsonResponder::class)->response(null, status: 202);

    expect($response->getStatusCode())->toBe(202)
        ->and($response->getData(true)['data'])->toBeNull();
});

test('formats a created response with HTTP 201', function () {
    $response = app(JsonResponder::class)->created(['id' => 5]);

    expect($response->getStatusCode())->toBe(201)
        ->and($response->getData(true))->toBe([
            'data' => ['id' => 5],
            'meta' => [],
            'message' => 'Created',
        ]);
});

test('formats an error with a meaningful HTTP status', function () {
    $response = app(JsonResponder::class)->error(
        'Validation failed',
        422,
        ['email' => ['Required']],
    );

    expect($response->getStatusCode())->toBe(422)
        ->and($response->getData(true))->toBe([
            'message' => 'Validation failed',
            'errors' => ['email' => ['Required']],
        ]);
});

test('uses Laravel length-aware paginator metadata unchanged', function () {
    $paginator = new LengthAwarePaginator(
        items: [['id' => 1], ['id' => 2]],
        total: 7,
        perPage: 2,
        currentPage: 1,
    );
    $expected = $paginator->toArray();
    unset($expected['data']);

    $response = app(JsonResponder::class)->paginated($paginator)->getData(true);

    expect($response['data'])->toBe([['id' => 1], ['id' => 2]])
        ->and($response['meta']['pagination'])->toBe($expected)
        ->and($response['meta']['pagination']['total'])->toBe(7)
        ->and($response['meta']['pagination']['last_page'])->toBe(4);
});

test('supports simple pagination without fake total counts', function () {
    $paginator = new Paginator(
        items: [['id' => 1], ['id' => 2], ['id' => 3]],
        perPage: 2,
        currentPage: 1,
    );

    $response = app(JsonResponder::class)->paginated($paginator)->getData(true);

    expect($response['data'])->toHaveCount(2)
        ->and($response['meta']['pagination'])->toHaveKeys(['current_page', 'next_page_url'])
        ->and($response['meta']['pagination'])->not->toHaveKeys(['total', 'last_page']);
});

test('supports cursor pagination', function () {
    $paginator = new CursorPaginator(
        items: [['id' => 1], ['id' => 2], ['id' => 3]],
        perPage: 2,
        options: ['parameters' => ['id']],
    );

    $response = app(JsonResponder::class)->paginated($paginator)->getData(true);

    expect($response['data'])->toHaveCount(2)
        ->and($response['meta']['pagination'])->toHaveKeys([
            'next_cursor', 'prev_cursor', 'next_page_url',
        ])
        ->and($response['meta']['pagination'])->not->toHaveKey('total');
});

test('accepts paginator-transformed items via Laravel through()', function () {
    $paginator = new LengthAwarePaginator(
        items: [['id' => 1]],
        total: 3,
        perPage: 1,
        currentPage: 1,
    );
    $paginator->through(fn (array $item) => ['user_id' => $item['id']]);

    $response = app(JsonResponder::class)->paginated($paginator)->getData(true);

    expect($response['data'])->toBe([['user_id' => 1]])
        ->and($response['meta']['pagination']['total'])->toBe(3);
});

test('does not intercept request headers or register middleware', function () {
    $request = \Illuminate\Http\Request::create('/test');
    $request->headers->set('Accept', 'text/html');

    app()->instance('request', $request);
    app(JsonResponder::class)->response(['id' => 1]);

    expect($request->header('Accept'))->toBe('text/html');
});
