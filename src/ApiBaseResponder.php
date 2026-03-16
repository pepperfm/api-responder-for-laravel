<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

class ApiBaseResponder implements ResponseContract
{
    /** @var array<string, string> $headers */
    private array $headers = ['Content-Type' => 'application/json; charset=UTF-8', 'charset' => 'utf-8'];

    public static function make(): static
    {
        return new static();
    }

    /*
     * Create a fluent ResponseBuilder for fine-grained control.
     *
     * Usage:
     *   $this->json->build()->forMethod('index')->response($data);
     *   $this->json->build()->withDataKey('users')->response($data);
     *   $this->json->build()->withoutWrapping()->response($data);
     */
    public function build(): ResponseBuilder
    {
        return ResponseBuilder::make();
    }

    /**
     * Shortcut: set explicit REST method name on a new builder.
     *
     * @param string $methodName
     */
    public function forMethod(string $methodName): ResponseBuilder
    {
        return $this->build()->forMethod($methodName);
    }

    /**
     * Shortcut: set explicit data key on a new builder.
     *
     * @param string $key
     */
    public function withDataKey(string $key): ResponseBuilder
    {
        return $this->build()->withDataKey($key);
    }

    /**
     * Shortcut: configure builder from PHP attributes on the calling controller method.
     *
     * @param class-string|null $class
     * @param string|null $method
     */
    public function fromAction(?string $class = null, ?string $method = null): ResponseBuilder
    {
        if ($class === null || $method === null) {
            $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
            $class ??= $caller['class'];
            $method ??= $caller['function'];
        }

        return $this->build()->fromAction($class, $method);
    }

    /**
     * @inheritdoc
     */
    public function response(
        array $data,
        array $meta = [],
        string $message = 'Success',
        int $httpStatusCode = JsonResponse::HTTP_OK
    ): JsonResponse {
        return $this->buildJsonResponse($data, $meta, $message, $httpStatusCode);
    }

    /**
     * @inheritdoc
     */
    public function error(
        string $message = 'Error',
        int $httpStatusCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
        mixed $errors = null,
    ): JsonResponse {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $httpStatusCode, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @inheritdoc
     */
    public function paginated(
        array|LengthAwarePaginator|CursorPaginator $data,
        array|LengthAwarePaginator|CursorPaginator $meta = [],
        string $message = 'Success',
        int $httpStatusCode = JsonResponse::HTTP_OK
    ): JsonResponse {
        $metaData = MetaResolver::resolve($data, $meta);

        return $this->response($metaData['data'], $metaData['meta'], $message, $httpStatusCode);
    }

    /**
     * @inheritdoc
     */
    public function stored(
        array $data = [],
        array $meta = [],
        string $message = 'Stored',
    ): JsonResponse {
        return $this->buildJsonResponse($data, $meta, $message, JsonResponse::HTTP_CREATED);
    }

    /**
     * @inheritdoc
     */
    public function deleted(
        array $data = [],
        string $message = 'Deleted',
    ): JsonResponse {
        return $this->buildJsonResponse($data, [], $message, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Core JSON response builder — single place for all response construction.
     * Uses config-based data key resolution (no debug_backtrace).
     *
     * @param array $data
     * @param array $meta
     * @param string $message
     * @param int $httpStatusCode
     */
    private function buildJsonResponse(
        array $data,
        array $meta,
        string $message,
        int $httpStatusCode,
    ): JsonResponse {
        $withoutWrapping = config('laravel-api-responder.without_wrapping', false);
        $key = config('laravel-api-responder.plural_data_key', 'entities');

        $formatted = $withoutWrapping ? $data : [$key => $data];

        return response()->json([
            ...$formatted,
            'meta' => $meta,
            'message' => $message,
        ], $httpStatusCode, $this->headers, JSON_UNESCAPED_UNICODE);
    }
}
