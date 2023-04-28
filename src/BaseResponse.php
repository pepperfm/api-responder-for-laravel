<?php

namespace Pepperfm\ApiBaseResponder;

use Illuminate\Support\Facades\Facade;

/**
 * @method \Illuminate\Http\JsonResponse response(array $data, string $message = '', int $httpStatusCode = 200)
 * @method \Illuminate\Http\JsonResponse error(string $message = '', int $httpStatusCode = 500, mixed $errors = null, mixed $data = null)
 * @method \Illuminate\Http\JsonResponse stored(array $data, string $message = '')
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
        return 'api-base-responder';
    }
}
