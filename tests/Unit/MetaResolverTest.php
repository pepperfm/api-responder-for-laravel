<?php

declare(strict_types=1);

use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Pepperfm\ApiBaseResponder\MetaResolver;

test('plain arrays remain unchanged', function () {
    expect(MetaResolver::resolve(['x'], ['source' => 'test']))->toBe([
        'data' => ['x'],
        'meta' => ['source' => 'test'],
    ]);
});

test('collections are accepted as data and meta', function () {
    $items = collect(['x', 'y']);

    $result = MetaResolver::resolve($items, collect(['foo' => 'bar']));

    expect($result['data'])->toBe($items)
        ->and($result['meta'])->toBe(['foo' => 'bar']);
});

test('paginator data are converted to serializable arrays', function () {
    $paginator = new LengthAwarePaginator([['id' => 1]], 4, 1, 1);

    $result = MetaResolver::resolve($paginator, ['source' => 'unit']);

    expect($result['data'])->toBe([['id' => 1]])
        ->and($result['meta']['source'])->toBe('unit')
        ->and($result['meta']['pagination']['total'])->toBe(4);
});

test('native paginator metadata is reused without custom field mapping', function () {
    $paginator = new LengthAwarePaginator([['id' => 1]], 4, 1, 1);
    $native = $paginator->toArray();
    unset($native['data']);

    expect(MetaResolver::extractPagination($paginator))->toBe($native);
});

test('simple pagination excludes total and last_page', function () {
    $paginator = new Paginator([['id' => 1], ['id' => 2]], 1, 1);

    expect(MetaResolver::extractPagination($paginator))
        ->toHaveKeys(['per_page', 'next_page_url'])
        ->not->toHaveKeys(['total', 'last_page']);
});

test('cursor pagination retains cursor information', function () {
    $paginator = new CursorPaginator(
        [['id' => 1], ['id' => 2], ['id' => 3]],
        2,
        options: ['parameters' => ['id']],
    );

    expect(MetaResolver::extractPagination($paginator))
        ->toHaveKeys(['next_cursor', 'prev_cursor']);
});
