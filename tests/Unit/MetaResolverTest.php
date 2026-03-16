<?php

declare(strict_types=1);

use Pepperfm\ApiBaseResponder\MetaResolver;

test('resolve with plain arrays', function () {
    $result = MetaResolver::resolve(['item1', 'item2'], ['key' => 'value']);

    expect($result)
        ->toHaveKeys(['data', 'meta'])
        ->and($result['data'])->toBe(['item1', 'item2'])
        ->and($result['meta'])->toBe(['key' => 'value']);
});

test('resolve with collections', function () {
    $data = collect(['item1', 'item2']);
    $meta = collect(['key' => 'value']);

    $result = MetaResolver::resolve($data, $meta);

    expect($result)
        ->toHaveKeys(['data', 'meta'])
        ->and($result['data'])->toEqual($data)
        ->and($result['meta'])->toBe(['key' => 'value']);
});

test('resolve with empty arrays', function () {
    $result = MetaResolver::resolve([], []);

    expect($result['data'])->toBe([])
        ->and($result['meta'])->toBe([]);
});

test('invokable returns same as static resolve', function () {
    $data = ['item1', 'item2'];
    $meta = ['key' => 'value'];

    $resolver = new MetaResolver($data, $meta);
    $invokeResult = $resolver();
    $staticResult = MetaResolver::resolve($data, $meta);

    expect($invokeResult)->toBe($staticResult);
});

test('extractPagination with LengthAwarePaginator returns correct keys', function () {
    $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
        items: [['id' => 1], ['id' => 2]],
        total: 10,
        perPage: 2,
        currentPage: 1
    );

    $result = MetaResolver::extractPagination($paginator);

    expect($result)
        ->toHaveKeys([
            'current_page',
            'per_page',
            'last_page',
            'from',
            'to',
            'total',
            'prev_page_url',
            'next_page_url',
            'links',
        ])
        ->and($result['current_page'])->toBe(1)
        ->and($result['per_page'])->toBe(2)
        ->and($result['total'])->toBe(10)
        ->and($result['last_page'])->toBe(5);
});

test('extractPagination with CursorPaginator returns correct keys', function () {
    $paginator = new \Illuminate\Pagination\CursorPaginator(
        items: [['id' => 1], ['id' => 2]],
        perPage: 2,
    );

    $result = MetaResolver::extractPagination($paginator);

    expect($result)
        ->toHaveKeys([
            'path',
            'per_page',
            'next_cursor',
            'next_page_url',
            'prev_cursor',
            'prev_page_url',
        ])
        ->and($result['per_page'])->toBe(2);
});
