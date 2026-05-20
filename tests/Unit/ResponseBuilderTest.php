<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Pepperfm\ApiBaseResponder\ResponseBuilder;

test('response with explicit data key', function () {
    $builder = ResponseBuilder::make()->withDataKey('users');

    $response = $builder->response(['name' => 'John']);
    $data = $response->getData(true);

    expect($data)
        ->toHaveKeys(['users', 'meta', 'message'])
        ->and($data['users'])->toBe(['name' => 'John'])
        ->and($data['message'])->toBe('Success');

    expect($response->getStatusCode())->toBe(JsonResponse::HTTP_OK);
});

test('response with forMethod singular', function () {
    config()->set('laravel-api-responder.using_for_rest', true);

    $builder = ResponseBuilder::make()->forMethod('show');
    $response = $builder->response(['id' => 1, 'name' => 'John']);
    $data = $response->getData(true);

    expect($data)->toHaveKeys(['entity', 'meta', 'message']);
});

test('response with forMethod plural', function () {
    config()->set('laravel-api-responder.using_for_rest', true);

    $builder = ResponseBuilder::make()->forMethod('index');
    $response = $builder->response([['id' => 1], ['id' => 2]]);
    $data = $response->getData(true);

    expect($data)->toHaveKeys(['entities', 'meta', 'message']);
});

test('response with forMethod update is singular', function () {
    config()->set('laravel-api-responder.using_for_rest', true);

    $builder = ResponseBuilder::make()->forMethod('update');
    $response = $builder->response(['id' => 1]);
    $data = $response->getData(true);

    expect($data)->toHaveKeys(['entity', 'meta', 'message']);
});

test('response with forMethod respects using_for_rest=false', function () {
    config()->set('laravel-api-responder.using_for_rest', false);

    $builder = ResponseBuilder::make()->forMethod('show');
    $response = $builder->response(['id' => 1]);
    $data = $response->getData(true);

    expect($data)->toHaveKeys(['entities', 'meta', 'message']);
});

test('response withoutWrapping spreads data into root', function () {
    $builder = ResponseBuilder::make()->withoutWrapping();
    $response = $builder->response(['id' => 1, 'name' => 'John']);
    $data = $response->getData(true);

    expect($data)
        ->toHaveKeys(['id', 'name', 'meta', 'message'])
        ->not->toHaveKey('entities');
});

test('response withoutWrapping via config', function () {
    config()->set('laravel-api-responder.without_wrapping', true);

    $builder = ResponseBuilder::make();
    $response = $builder->response(['id' => 1, 'name' => 'John']);
    $data = $response->getData(true);

    expect($data)
        ->toHaveKeys(['id', 'name', 'meta', 'message'])
        ->not->toHaveKey('entities');
});

test('stored returns HTTP 201', function () {
    $builder = ResponseBuilder::make()->withDataKey('user');
    $response = $builder->stored(['id' => 1, 'name' => 'John']);
    $data = $response->getData(true);

    expect($response->getStatusCode())->toBe(JsonResponse::HTTP_CREATED)
        ->and($data['message'])->toBe('Stored')
        ->and($data)->toHaveKey('user');
});

test('deleted returns HTTP 204', function () {
    $builder = ResponseBuilder::make();
    $response = $builder->deleted();
    $data = $response->getData(true);

    expect($response->getStatusCode())->toBe(JsonResponse::HTTP_NO_CONTENT)
        ->and($data['message'])->toBe('Deleted');
});

test('custom data keys via config', function () {
    config()->set('laravel-api-responder.plural_data_key', 'items');
    config()->set('laravel-api-responder.singular_data_key', 'item');

    $plural = ResponseBuilder::make()->forMethod('index')->response([]);
    $singular = ResponseBuilder::make()->forMethod('show')->response([]);

    expect($plural->getData(true))->toHaveKey('items')
        ->and($singular->getData(true))->toHaveKey('item');
});

test('response with custom message and status code', function () {
    $builder = ResponseBuilder::make()->withDataKey('result');
    $response = $builder->response(
        ['key' => 'value'],
        ['page' => 1],
        'Custom message',
        JsonResponse::HTTP_ACCEPTED
    );
    $data = $response->getData(true);

    expect($response->getStatusCode())->toBe(JsonResponse::HTTP_ACCEPTED)
        ->and($data['message'])->toBe('Custom message')
        ->and($data['meta'])->toBe(['page' => 1])
        ->and($data['result'])->toBe(['key' => 'value']);
});

test('raw returns payload without envelope fields', function () {
    $builder = ResponseBuilder::make()->withDataKey('ignored');
    $response = $builder->raw([
        ['id' => 1],
        ['id' => 2],
    ], JsonResponse::HTTP_ACCEPTED);
    $data = $response->getData(true);

    expect($response->getStatusCode())->toBe(JsonResponse::HTTP_ACCEPTED)
        ->and($data)->toBe([
            ['id' => 1],
            ['id' => 2],
        ])
        ->and($data)->not->toHaveKeys(['ignored', 'entities', 'meta', 'message']);
});

test('fromAction reads #[ResponseDataKey] attribute', function () {
    $builder = ResponseBuilder::make()->fromAction(
        \Pepperfm\ApiBaseResponder\Tests\Fixtures\AttributeController::class,
        'customKey'
    );

    $response = $builder->response(['id' => 1]);
    $data = $response->getData(true);

    expect($data)->toHaveKey('my_items');
});

test('fromAction reads #[ResponseDataKey] without param defaults to entity', function () {
    $builder = ResponseBuilder::make()->fromAction(
        \Pepperfm\ApiBaseResponder\Tests\Fixtures\AttributeController::class,
        'defaultKey'
    );

    $response = $builder->response(['id' => 1]);
    $data = $response->getData(true);

    expect($data)->toHaveKey('entity');
});

test('fromAction reads #[WithoutWrapping] attribute', function () {
    $builder = ResponseBuilder::make()->fromAction(
        \Pepperfm\ApiBaseResponder\Tests\Fixtures\AttributeController::class,
        'noWrap'
    );

    $response = $builder->response(['id' => 1, 'name' => 'John']);
    $data = $response->getData(true);

    expect($data)
        ->toHaveKeys(['id', 'name', 'meta', 'message'])
        ->not->toHaveKey('entities');
});

test('fromAction uses REST convention when no attributes', function () {
    config()->set('laravel-api-responder.using_for_rest', true);

    $builder = ResponseBuilder::make()->fromAction(
        \Pepperfm\ApiBaseResponder\Tests\Fixtures\AttributeController::class,
        'show'
    );

    $response = $builder->response(['id' => 1]);
    $data = $response->getData(true);

    expect($data)->toHaveKey('entity');
});

test('fromAction via ApiBaseResponder shortcut', function () {
    $responder = app(\Pepperfm\ApiBaseResponder\Contracts\ResponseContract::class);

    $response = $responder->fromAction(
        \Pepperfm\ApiBaseResponder\Tests\Fixtures\AttributeController::class,
        'customKey'
    )->response(['id' => 1]);

    $data = $response->getData(true);

    expect($data)->toHaveKey('my_items');
});

test('fromAction auto-detects caller — #[ResponseDataKey]', function () {
    /** @var \Pepperfm\ApiBaseResponder\Tests\Fixtures\AttributeController $controller */
    $controller = $this->app->make(\Pepperfm\ApiBaseResponder\Tests\Fixtures\AttributeController::class);

    $data = $controller->customKey()->getData(true);

    expect($data)->toHaveKey('my_items');
});

test('fromAction auto-detects caller — #[WithoutWrapping]', function () {
    /** @var \Pepperfm\ApiBaseResponder\Tests\Fixtures\AttributeController $controller */
    $controller = $this->app->make(\Pepperfm\ApiBaseResponder\Tests\Fixtures\AttributeController::class);

    $data = $controller->noWrap()->getData(true);

    expect($data)
        ->toHaveKeys(['id', 'name', 'meta', 'message'])
        ->not->toHaveKey('entities');
});

test('fromAction auto-detects caller — REST convention for show', function () {
    config()->set('laravel-api-responder.using_for_rest', true);

    /** @var \Pepperfm\ApiBaseResponder\Tests\Fixtures\AttributeController $controller */
    $controller = $this->app->make(\Pepperfm\ApiBaseResponder\Tests\Fixtures\AttributeController::class);

    $data = $controller->show()->getData(true);

    expect($data)->toHaveKey('entity');
});

test('builder via ApiBaseResponder build()', function () {
    /** @var \Pepperfm\ApiBaseResponder\Contracts\ResponseContract $responder */
    $responder = app(\Pepperfm\ApiBaseResponder\Contracts\ResponseContract::class);

    $response = $responder->build()->withDataKey('users')->response([['id' => 1]]);
    $data = $response->getData(true);

    expect($data)->toHaveKeys(['users', 'meta', 'message']);
});

test('builder via ApiBaseResponder forMethod() shortcut', function () {
    /** @var \Pepperfm\ApiBaseResponder\Contracts\ResponseContract $responder */
    $responder = app(\Pepperfm\ApiBaseResponder\Contracts\ResponseContract::class);

    $response = $responder->forMethod('show')->response(['id' => 1]);
    $data = $response->getData(true);

    expect($data)->toHaveKeys(['entity', 'meta', 'message']);
});

test('builder via ApiBaseResponder withDataKey() shortcut', function () {
    /** @var \Pepperfm\ApiBaseResponder\Contracts\ResponseContract $responder */
    $responder = app(\Pepperfm\ApiBaseResponder\Contracts\ResponseContract::class);

    $response = $responder->withDataKey('products')->response([]);
    $data = $response->getData(true);

    expect($data)->toHaveKeys(['products', 'meta', 'message']);
});
