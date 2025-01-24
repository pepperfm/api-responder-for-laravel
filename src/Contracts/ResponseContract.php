<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @method JsonResponse paginate(array|Arrayable|LengthAwarePaginator|CursorPaginator $data, array|Arrayable|LengthAwarePaginator|CursorPaginator $meta = [], string $message = 'Success', int $httpStatusCode = JsonResponse::HTTP_OK)
 * @method JsonResponse stored(array|Arrayable $data, array $meta = [], string $message = '')
 * @method JsonResponse deleted(array $data, string $message = '')
 *
 * @mixin \Pepperfm\ApiBaseResponder\ApiBaseResponder
 */
interface ResponseContract
{
    /**
     * Success response method
     *
     * @param array|Arrayable $data
     * @param array $meta
     * @param string $message
     * @param int $httpStatusCode
     *
     * @return JsonResponse
     */
    public function response(
        array|Arrayable $data,
        array $meta = [],
        string $message = 'Success',
        int $httpStatusCode = JsonResponse::HTTP_OK,
    ): JsonResponse;

    /**
     * Error response method
     *
     * @param string $message
     * @param int $httpStatusCode
     * @param ?mixed $errors
     *
     * @return JsonResponse
     */
    public function error(
        string $message = 'Error',
        int $httpStatusCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
        mixed $errors = null,
    ): JsonResponse;
}
