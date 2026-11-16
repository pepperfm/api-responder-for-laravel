<?php

declare(strict_types=1);

namespace Pepperfm\ApiResponder;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

final class JsonResponder
{
    public function response(
        mixed $data,
        array $meta = [],
        string $message = 'Success',
        int $status = 200,
    ): JsonResponse {
        return response()->json([
            'data' => $data,
            'meta' => $meta,
            'message' => $message,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }

    public function created(mixed $data, array $meta = []): JsonResponse
    {
        return $this->response($data, $meta, 'Created', 201);
    }

    /**
     * Use the paginator's own serialization to keep Laravel's metadata intact.
     * Map the paginator's items with ->through() before calling this method.
     */
    public function paginated(
        LengthAwarePaginator|Paginator|CursorPaginator $paginator,
    ): JsonResponse {
        $pagination = $paginator->toArray();
        $data = $pagination['data'];
        unset($pagination['data']);

        return $this->response($data, ['pagination' => $pagination]);
    }

    public function error(
        string $message,
        int $status = 500,
        array $errors = [],
    ): JsonResponse {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }
}
