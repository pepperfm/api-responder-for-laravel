<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;
use Pepperfm\ApiBaseResponder\Facades\BaseResponse;
use Pepperfm\ApiBaseResponder\Tests\Fixtures\ExampleController;

use function Pest\Laravel\get;

test('actual responder formats an envelope', function () {
    $response = app(ResponseContract::class)->response(
        collect([['id' => 1]]),
        ['foo' => 'bar'],
        'Done',
    );

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getData(true))->toBe([
            'entities' => [['id' => 1]],
            'meta' => ['foo' => 'bar'],
            'message' => 'Done',
        ]);
});

test('configurable singular and plural keys remain available', function () {
    config()->set('laravel-api-responder.plural_data_key', 'items');
    config()->set('laravel-api-responder.singular_data_key', 'item');

    $responder = app(ResponseContract::class);

    expect($responder->response(['id' => 1])->getData(true))->toHaveKey('items')
        ->and($responder->forMethod('show')->response(['id' => 1])->getData(true))->toHaveKey('item')
        ->and($responder->withDataKey('custom')->response(['id' => 1])->getData(true))->toHaveKey('custom');
});

test('REST action naming is opt-in and respects config', function () {
    $responder = app(ResponseContract::class);

    expect($responder->response(['id' => 1])->getData(true))->toHaveKey('entities')
        ->and($responder->forMethod('show')->response(['id' => 1])->getData(true))->toHaveKey('entity');

    config()->set('laravel-api-responder.using_for_rest', false);

    expect($responder->forMethod('show')->response(['id' => 1])->getData(true))->toHaveKey('entities');
});

test('real controller works through method-based builder', function () {
    $controller = app(ExampleController::class);

    expect($controller->index()->getData(true))->toHaveKey('entities')
        ->and($controller->show()->getData(true))->toHaveKey('entity')
        ->and($controller->update()->getData(true))->toHaveKey('entity');
});

test('raw accepts collections and preserves the exact payload', function () {
    $response = app(ResponseContract::class)->raw(collect([
        'id' => 1,
        'message' => 'untouched',
    ]));

    expect($response->getData(true))->toBe(['id' => 1, 'message' => 'untouched']);
});

test('error response uses the requested status and validation details', function () {
    $response = app(ResponseContract::class)->error('Invalid', 422, ['email' => ['Required']]);

    expect($response->getStatusCode())->toBe(422)
        ->and($response->getData(true))->toBe([
            'message' => 'Invalid',
            'errors' => ['email' => ['Required']],
        ]);
});

test('stored supports Laravel collections and responds with 201', function () {
    $response = app(ResponseContract::class)->stored(collect(['id' => 42]));

    expect($response->getStatusCode())->toBe(JsonResponse::HTTP_CREATED)
        ->and($response->getData(true)['entities'])->toBe(['id' => 42]);
});

test('deleted returns an actually empty HTTP 204', function () {
    $response = app(ResponseContract::class)->deleted();

    expect($response->getStatusCode())->toBe(JsonResponse::HTTP_NO_CONTENT)
        ->and($response->getContent())->toBe('');
});

test('length aware paginator can be passed directly', function () {
    $paginator = new LengthAwarePaginator(
        items: [['id' => 1], ['id' => 2]],
        total: 8,
        perPage: 2,
        currentPage: 1,
    );
    $response = app(ResponseContract::class)->paginated($paginator)->getData(true);

    expect($response['entities'])->toBe([['id' => 1], ['id' => 2]])
        ->and($response['meta']['pagination']['total'])->toBe(8)
        ->and($response['meta']['pagination']['last_page'])->toBe(4);
});

test('simple paginator works and does not pretend to have totals', function () {
    $paginator = new Paginator(
        items: [['id' => 1], ['id' => 2], ['id' => 3]],
        perPage: 2,
        currentPage: 1,
    );

    $response = app(ResponseContract::class)->paginated($paginator)->getData(true);

    expect($response['entities'])->toHaveCount(2)
        ->and($response['meta']['pagination'])->toHaveKeys(['next_page_url', 'current_page'])
        ->and($response['meta']['pagination'])->not->toHaveKey('total');
});

test('cursor paginator works', function () {
    $paginator = new CursorPaginator(
        items: [['id' => 1], ['id' => 2], ['id' => 3]],
        perPage: 2,
        options: ['parameters' => ['id']],
    );

    $response = app(ResponseContract::class)->paginated($paginator)->getData(true);

    expect($response['entities'])->toHaveCount(2)
        ->and($response['meta']['pagination'])->toHaveKeys(['next_cursor', 'next_page_url']);
});

test('mapped collection can be paired with the original paginator', function () {
    $paginator = new LengthAwarePaginator([['id' => 1]], 12, 1, 1);
    $mapped = collect([['username' => 'anna']]);

    $response = app(ResponseContract::class)->paginated($mapped, $paginator)->getData(true);

    expect($response['entities'])->toBe([['username' => 'anna']])
        ->and($response['meta']['pagination']['total'])->toBe(12);
});

test('custom metadata survives pagination', function () {
    $paginator = new LengthAwarePaginator([['id' => 1]], 1, 1, 1);

    $response = app(ResponseContract::class)->paginated($paginator, [
        'request_id' => 'abc',
    ])->getData(true);

    expect($response['meta']['request_id'])->toBe('abc')
        ->and($response['meta']['pagination']['total'])->toBe(1);
});

test('facade remains supported', function () {
    expect(BaseResponse::withDataKey('people')->response([['id' => 1]])->getData(true))
        ->toHaveKey('people');
});

test('the package does not overwrite request Accept headers', function () {
    $response = get(route('api.accept'), ['Accept' => 'text/html']);

    expect($response->json('accept'))->toBe('text/html')
        ->and($response->headers->get('content-type'))->toContain('application/json');
});

test('response content type remains JSON', function () {
    $response = get(route('api.users.index'));

    expect($response->headers->get('content-type'))->toContain('application/json');
});
