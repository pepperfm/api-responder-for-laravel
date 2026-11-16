<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;
use Pepperfm\ApiBaseResponder\Facades\BaseResponse;
use Pepperfm\ApiBaseResponder\Tests\Fixtures\ExampleController;

use function Pest\Laravel\get;

test('the real responder returns the standard JSON envelope', function () {
    $responder = app(ResponseContract::class);

    $response = $responder->response([['id' => 1]], ['source' => 'test'], 'OK');

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getData(true))->toBe([
            'entities' => [['id' => 1]],
            'meta' => ['source' => 'test'],
            'message' => 'OK',
        ]);
});

test('the direct API accepts Laravel collections', function () {
    $responder = app(ResponseContract::class);

    expect($responder->response(collect([['id' => 1]]))->getData(true)['entities'])
        ->toBe([['id' => 1]])
        ->and($responder->raw(collect(['id' => 1]))->getData(true))->toBe(['id' => 1]);
});

test('REST key selection only happens with an explicit builder context', function () {
    $responder = app(ResponseContract::class);

    expect($responder->response(['id' => 1])->getData(true))->toHaveKey('entities')
        ->and($responder->forMethod('show')->response(['id' => 1])->getData(true))->toHaveKey('entity')
        ->and($responder->withDataKey('user')->response(['id' => 1])->getData(true))->toHaveKey('user');
});

test('flexible data keys', function (string $method, string $expectedKey, bool $configState = true) {
    config()->set('laravel-api-responder.using_for_rest', $configState);
    $controller = app(ExampleController::class);

    expect($controller->$method()->getData(true))->toHaveKeys([$expectedKey, 'meta', 'message']);
})->with([
    'ON: plural for index' => ['index', 'entities'],
    'ON: plural for arbitrary method' => ['omg', 'entities'],
    'ON: singular for show' => ['show', 'entity'],
    'ON: singular for update' => ['update', 'entity'],
    'OFF: plural for show' => ['show', 'entities', false],
    'OFF: plural for update' => ['update', 'entities', false],
    'explicit entity key' => ['attributeWithoutParam', 'entity'],
    'explicit random key' => ['attributeWithParam', 'random_key'],
]);

test('request accept headers are not modified by the package', function () {
    $response = get(route('api.accept'), ['Accept' => 'text/html']);

    expect($response->json('accept'))->toBe('text/html')
        ->and($response->headers->get('content-type'))->toContain('application/json');
});

test('standard responses still have JSON content type', function () {
    $response = get(route('api.users.index'), ['Accept' => 'text/html']);

    expect($response->headers->get('content-type'))->toContain('application/json');
});

test('raw returns the payload without any envelope', function () {
    $response = app(ResponseContract::class)->raw(['id' => 1, 'name' => 'John']);

    expect($response->getData(true))->toBe(['id' => 1, 'name' => 'John']);
});

test('error includes the status and validation details', function () {
    $response = app(ResponseContract::class)->error('Invalid', 422, ['email' => ['Required']]);

    expect($response->getStatusCode())->toBe(JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        ->and($response->getData(true))->toBe([
            'message' => 'Invalid',
            'errors' => ['email' => ['Required']],
        ]);
});

test('stored returns HTTP 201', function () {
    $response = app(ResponseContract::class)->stored(collect(['id' => 12]));

    expect($response->getStatusCode())->toBe(JsonResponse::HTTP_CREATED)
        ->and($response->getData(true)['entities'])->toBe(['id' => 12]);
});

test('deleted returns HTTP 204 with an empty body', function () {
    $responder = app(ResponseContract::class);
    $response = $responder->deleted(['id' => 12], 'Deleted');

    expect($response->getStatusCode())->toBe(JsonResponse::HTTP_NO_CONTENT)
        ->and($response->getContent())->toBe('');
});

test('paginated accepts LengthAwarePaginator directly', function () {
    $paginator = new LengthAwarePaginator(
        items: [['id' => 1], ['id' => 2]],
        total: 8,
        perPage: 2,
        currentPage: 1,
    );

    $result = app(ResponseContract::class)->paginated($paginator)->getData(true);

    expect($result['entities'])->toBe([['id' => 1], ['id' => 2]])
        ->and($result['meta']['pagination']['total'])->toBe(8)
        ->and($result['meta']['pagination']['last_page'])->toBe(4);
});

test('paginated accepts simple paginator directly', function () {
    $paginator = new Paginator(
        items: [['id' => 1], ['id' => 2], ['id' => 3]],
        perPage: 2,
        currentPage: 1,
    );

    $result = app(ResponseContract::class)->paginated($paginator)->getData(true);

    expect($result['entities'])->toHaveCount(2)
        ->and($result['meta']['pagination'])->toHaveKey('next_page_url')
        ->and($result['meta']['pagination'])->not->toHaveKey('total');
});

test('paginated accepts CursorPaginator directly', function () {
    $paginator = new CursorPaginator(
        items: [['id' => 1], ['id' => 2], ['id' => 3]],
        perPage: 2,
    );

    $result = app(ResponseContract::class)->paginated($paginator)->getData(true);

    expect($result['entities'])->toHaveCount(2)
        ->and($result['meta']['pagination'])->toHaveKeys(['per_page', 'next_cursor', 'next_page_url']);
});

test('mapped collections can be paired with original pagination', function () {
    $paginator = new LengthAwarePaginator([['id' => 1]], 5, 1, 2);
    $mapped = collect([['label' => 'One']]);

    $result = app(ResponseContract::class)->paginated($mapped, $paginator)->getData(true);

    expect($result['entities'])->toBe([['label' => 'One']])
        ->and($result['meta']['pagination']['total'])->toBe(5);
});

test('the facade resolves the same contract', function () {
    expect(BaseResponse::withDataKey('user')->response(['id' => 1])->getData(true))
        ->toHaveKey('user');
});
