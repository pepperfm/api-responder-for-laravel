<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Pepperfm\ApiBaseResponder\ResponseBuilder;

/**
 * @mixin \Pepperfm\ApiBaseResponder\ApiBaseResponder
 */
interface ResponseContract
{
    /**
     * Create a fluent response builder for explicit response construction.
     *
     * @return ResponseBuilder
     */
    public function build(): ResponseBuilder;

    /**
     * Create a builder configured with an explicit REST method name.
     *
     * @param string $methodName REST action name used for data-key resolution
     *
     * @return ResponseBuilder
     */
    public function forMethod(string $methodName): ResponseBuilder;

    /**
     * Create a builder configured with an explicit response data key.
     *
     * @param string $key Response data key
     *
     * @return ResponseBuilder
     */
    public function withDataKey(string $key): ResponseBuilder;

    /**
     * Create a builder configured from PHP attributes on a controller action.
     *
     * @param class-string|null $class Controller/action class name
     * @param string|null $method Controller/action method name
     *
     * @return ResponseBuilder
     */
    public function fromAction(?string $class = null, ?string $method = null): ResponseBuilder;

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
     * Raw JSON response.
     *
     * Returns the payload directly as the JSON body without data-key wrapping,
     * meta, or message fields.
     *
     * @param array $data Response payload
     * @param int $httpStatusCode HTTP status code
     *
     * @return JsonResponse
     */
    public function raw(
        array $data,
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

    /**
     * Success response with pagination metadata resolved from paginator input.
     *
     * @param array|CursorPaginator|LengthAwarePaginator $data Response payload or paginator
     * @param array|CursorPaginator|LengthAwarePaginator $meta Additional metadata or paginator
     * @param string $message Human-readable message
     * @param int $httpStatusCode HTTP status code
     *
     * @return JsonResponse
     */
    public function paginated(
        array|CursorPaginator|LengthAwarePaginator $data,
        array|CursorPaginator|LengthAwarePaginator $meta = [],
        string $message = 'Success',
        int $httpStatusCode = JsonResponse::HTTP_OK,
    ): JsonResponse;

    /**
     * Stored response.
     *
     * @param array $data Response payload
     * @param array $meta Additional metadata
     * @param string $message Human-readable message
     *
     * @return JsonResponse
     */
    public function stored(
        array $data = [],
        array $meta = [],
        string $message = 'Stored',
    ): JsonResponse;

    /**
     * Deleted response.
     *
     * @param array $data Response payload
     * @param string $message Human-readable message
     *
     * @return JsonResponse
     */
    public function deleted(
        array $data = [],
        string $message = 'Deleted',
    ): JsonResponse;
}
