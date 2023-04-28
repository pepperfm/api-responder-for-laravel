<?php

namespace Pepperfm\ApiBaseResponder;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Http\JsonResponse response(array $data, string $message = 'Success', int $httpStatusCode = \Illuminate\Http\JsonResponse::HTTP_OK)
 * @method static \Illuminate\Http\JsonResponse error(string $message = 'Error', int $httpStatusCode = \Illuminate\Http\JsonResponse::HTTP_INTERNAL_SERVER_ERROR, mixed $errors = null, mixed $data = null)
 * @method static \Illuminate\Http\JsonResponse stored(array $data, string $message = '')
 * @method static \Illuminate\Http\JsonResponse deleted(array $data, string $message = '')
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
        return 'api-base-responder';
    }
}
