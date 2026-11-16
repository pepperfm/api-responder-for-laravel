<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Tests\Fixtures;

use Illuminate\Http\JsonResponse;
use Pepperfm\ApiBaseResponder\Attributes\ResponseDataKey;
use Pepperfm\ApiBaseResponder\Attributes\WithoutWrapping;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;
use Pepperfm\ApiBaseResponder\Facades\BaseResponse;

/*
 * Test fixture for #[ResponseDataKey] and #[WithoutWrapping] attribute tests.
 */
class AttributeController
{
    public function __construct(public ResponseContract $json)
    {
    }

    #[ResponseDataKey('my_items')]
    public function customKey(): JsonResponse
    {
        return $this->json->fromAction()->response(['id' => 1]);
    }

    #[ResponseDataKey]
    public function defaultKey(): JsonResponse
    {
        return $this->json->fromAction()->response(['id' => 1]);
    }

    #[WithoutWrapping]
    public function noWrap(): JsonResponse
    {
        return $this->json->fromAction()->response(['id' => 1, 'name' => 'John']);
    }

    #[ResponseDataKey('facade_items')]
    public function facadeKey(): JsonResponse
    {
        return BaseResponse::fromAction()->response(['id' => 10]);
    }

    public function show(): JsonResponse
    {
        return $this->json->fromAction()->response(['id' => 1]);
    }

    public function index(): JsonResponse
    {
        return $this->json->fromAction()->response(['id' => 1]);
    }
}
