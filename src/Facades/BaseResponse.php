<?php

namespace Pepperfm\ApiBaseResponder\Facades;

use Illuminate\Support\Facades\Facade;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;
use Illuminate\Support\Collection;

/**
 * @method \Illuminate\Http\JsonResponse response(array|Collection $data, array $meta = [], string $message = 'Success', int $httpStatusCode = \Illuminate\Http\JsonResponse::HTTP_OK)
 * @method \Illuminate\Http\JsonResponse error(string $message = 'Error', int $httpStatusCode = \Illuminate\Http\JsonResponse::HTTP_INTERNAL_SERVER_ERROR, mixed $errors = null, mixed $data = null)
 * @method \Illuminate\Http\JsonResponse stored(array $data, array $meta = [], string $message = '')
 * @method \Illuminate\Http\JsonResponse deleted(array $data, string $message = '')
 *
 * @see \Pepperfm\ApiBaseResponder\ApiBaseResponder
 */
class BaseResponse extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return ResponseContract::class;
    }
}
