<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\CursorPaginator;
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

    /**
     * @inheritdoc
     */
    public function response(
        array|Arrayable $data,
        array $meta = [],
        string $message = 'Success',
        int $httpStatusCode = JsonResponse::HTTP_OK
    ): JsonResponse {
        $callStackTrace = data_get(debug_backtrace(), '1');
        $callerFunction = new \ReflectionMethod($callStackTrace['class'], $callStackTrace['function']);

        $key = ValidateRestMethod::make()->getDataKey($callerFunction);

        if ($data instanceof Arrayable && (!$data instanceof CursorPaginator || !$data instanceof LengthAwarePaginator)) {
            $data = $data->toArray();
        }

        $withoutWrapping = config('laravel-api-responder.without_wrapping', false);
        $formated = $withoutWrapping ? $data : [$key => $data];

        return response()->json([
            ...$formated,
            'meta' => $meta,
            'message' => $message,
        ], $httpStatusCode, $this->headers, JSON_UNESCAPED_UNICODE);
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

    public function paginated(
        array|Arrayable|LengthAwarePaginator|CursorPaginator $data,
        array|Arrayable|LengthAwarePaginator|CursorPaginator $meta = [],
        string $message = 'Success',
        int $httpStatusCode = JsonResponse::HTTP_OK
    ): JsonResponse {
        $metaData = rescue(new MetaResolver($data, $meta));

        return $this->response($metaData['data'], $metaData['meta'], $message, $httpStatusCode);
    }

    /**
     * @param array $data
     * @param array $meta
     * @param string $message
     *
     * @return JsonResponse
     */
    public function stored(
        array $data = [],
        array $meta = [],
        string $message = 'Stored',
    ): JsonResponse {
        return $this->response($data, $meta, $message, JsonResponse::HTTP_CREATED);
    }

    /**
     * @param array $data
     * @param string $message
     *
     * @return JsonResponse
     */
    public function deleted(
        array $data = [],
        string $message = 'Deleted',
    ): JsonResponse {
        return $this->response($data, message: $message, httpStatusCode: JsonResponse::HTTP_NO_CONTENT);
    }
}
