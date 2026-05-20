<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;
use Pepperfm\ApiBaseResponder\ResponseBuilder;

/**
 * Builder API (explicit context):
 *
 * @method static ResponseBuilder build() Create a fluent ResponseBuilder
 * @method static ResponseBuilder forMethod(string $methodName) Builder with explicit REST method
 * @method static ResponseBuilder withDataKey(string $key) Builder with explicit data key
 * @method static ResponseBuilder fromAction(?string $class = null, ?string $method = null) Builder configured from PHP attributes
 *
 * Direct API (config-based key resolution):
 * @method static JsonResponse response(array $data, array $meta = [], string $message = 'Success', int $httpStatusCode = JsonResponse::HTTP_OK)
 * @method static JsonResponse raw(array $data, int $httpStatusCode = JsonResponse::HTTP_OK)
 * @method static JsonResponse paginated(array|LengthAwarePaginator|CursorPaginator $data, array|LengthAwarePaginator|CursorPaginator $meta = [], string $message = 'Success', int $httpStatusCode = JsonResponse::HTTP_OK)
 * @method static JsonResponse error(string $message = 'Error', int $httpStatusCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR, mixed $errors = null)
 * @method static JsonResponse stored(array $data = [], array $meta = [], string $message = 'Stored')
 * @method static JsonResponse deleted(array $data = [], string $message = 'Deleted')
 *
 * @see \Pepperfm\ApiBaseResponder\ApiBaseResponder
 */
class BaseResponse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ResponseContract::class;
    }
}
