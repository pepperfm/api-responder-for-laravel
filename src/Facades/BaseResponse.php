<?php

namespace Pepperfm\ApiBaseResponder\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

/**
 * @method JsonResponse response(array|Collection $data, array $meta = [], string $message = 'Success', int $httpStatusCode = JsonResponse::HTTP_OK)
 * @method JsonResponse paginate(array|LengthAwarePaginator $data, array|LengthAwarePaginator $meta = [], string $message = 'Success', int $httpStatusCode = JsonResponse::HTTP_OK)
 * @method JsonResponse error(string $message = 'Error', int $httpStatusCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR, mixed $errors = null, mixed $data = null)
 * @method JsonResponse stored(array $data, array $meta = [], string $message = '')
 * @method JsonResponse deleted(array $data, string $message = '')
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
