<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

class ApiBaseResponder implements ResponseContract
{
    public static function make(): static
    {
        return new static();
    }

    public function build(): ResponseBuilder
    {
        return ResponseBuilder::make();
    }

    public function forMethod(string $methodName): ResponseBuilder
    {
        return $this->build()->forMethod($methodName);
    }

    public function withDataKey(string $key): ResponseBuilder
    {
        return $this->build()->withDataKey($key);
    }

    public function fromAction(?string $class = null, ?string $method = null): ResponseBuilder
    {
        return $this->build()->fromAction($class, $method);
    }

    public function response(
        array|Arrayable $data,
        array $meta = [],
        string $message = 'Success',
        int $httpStatusCode = JsonResponse::HTTP_OK,
    ): JsonResponse {
        return $this->build()->response($data, $meta, $message, $httpStatusCode);
    }

    public function raw(
        array|Arrayable $data,
        int $httpStatusCode = JsonResponse::HTTP_OK,
    ): JsonResponse {
        return $this->build()->raw($data, $httpStatusCode);
    }

    public function error(
        string $message = 'Error',
        int $httpStatusCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
        mixed $errors = null,
    ): JsonResponse {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $httpStatusCode, [], JSON_UNESCAPED_UNICODE);
    }

    public function paginated(
        array|Arrayable|Paginator|LengthAwarePaginator|CursorPaginator $data,
        array|Arrayable|Paginator|LengthAwarePaginator|CursorPaginator $meta = [],
        string $message = 'Success',
        int $httpStatusCode = JsonResponse::HTTP_OK,
    ): JsonResponse {
        $resolved = MetaResolver::resolve($data, $meta);

        return $this->response($resolved['data'], $resolved['meta'], $message, $httpStatusCode);
    }

    public function stored(
        array|Arrayable $data = [],
        array $meta = [],
        string $message = 'Stored',
    ): JsonResponse {
        return $this->build()->stored($data, $meta, $message);
    }

    public function deleted(): JsonResponse
    {
        return $this->build()->deleted();
    }
}
