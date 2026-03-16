<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Pepperfm\ApiBaseResponder\ResponseBuilder;

/**
 * @method JsonResponse paginated(array|LengthAwarePaginator|CursorPaginator $data, array|LengthAwarePaginator|CursorPaginator $meta = [], string $message = 'Success', int $httpStatusCode = JsonResponse::HTTP_OK)
 * @method JsonResponse stored(array $data = [], array $meta = [], string $message = 'Stored')
 * @method JsonResponse deleted(array $data = [], string $message = 'Deleted')
 * @method ResponseBuilder build()
 * @method ResponseBuilder forMethod(string $methodName)
 * @method ResponseBuilder withDataKey(string $key)
 * @method ResponseBuilder fromAction(?string $class = null, ?string $method = null)
 *
 * @mixin \Pepperfm\ApiBaseResponder\ApiBaseResponder
 */
interface ResponseContract
{
    /**
     * Success response.
     *
     * Returns JSON: {"<data_key>": [...], "meta": [...], "message": "..."}
     * Data key resolved from config. Use ResponseBuilder for fine-grained control.
     *
     * @param array $data Response payload
     * @param array $meta Additional metadata
     * @param string $message Human-readable message
     * @param int $httpStatusCode HTTP status code
     *
     * @return JsonResponse
     */
    public function response(
        array $data,
        array $meta = [],
        string $message = 'Success',
        int $httpStatusCode = JsonResponse::HTTP_OK,
    ): JsonResponse;

    /**
     * Error response.
     *
     * Returns JSON: {"message": "...", "errors": ...}
     *
     * @param string $message Error message
     * @param int $httpStatusCode HTTP status code (default 500)
     * @param mixed $errors Error details (validation errors, exception info, etc.)
     *
     * @return JsonResponse
     */
    public function error(
        string $message = 'Error',
        int $httpStatusCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
        mixed $errors = null,
    ): JsonResponse;
}
