<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

final class MetaResolver
{
    /**
     * @return array{data: array|Arrayable, meta: array}
     */
    public static function resolve(
        array|Arrayable|Paginator|LengthAwarePaginator|CursorPaginator $data,
        array|Arrayable|Paginator|LengthAwarePaginator|CursorPaginator $meta = [],
    ): array {
        if (self::isPaginator($data)) {
            $additionalMeta = self::isPaginator($meta) ? [] : self::asArray($meta);

            return [
                'data' => $data->getCollection()->toArray(),
                'meta' => [
                    ...$additionalMeta,
                    'pagination' => self::extractPagination($data),
                ],
            ];
        }

        if (self::isPaginator($meta)) {
            return [
                'data' => $data,
                'meta' => ['pagination' => self::extractPagination($meta)],
            ];
        }

        return ['data' => $data, 'meta' => self::asArray($meta)];
    }

    /**
     * Keep the native Laravel paginator metadata; exclude data because it has
     * its own (configurable) top-level key in the responder envelope.
     */
    public static function extractPagination(Paginator|LengthAwarePaginator|CursorPaginator $paginator): array
    {
        $pagination = $paginator->toArray();
        unset($pagination['data']);

        return $pagination;
    }

    private static function isPaginator(mixed $value): bool
    {
        return $value instanceof Paginator ||
            $value instanceof LengthAwarePaginator ||
            $value instanceof CursorPaginator;
    }

    private static function asArray(array|Arrayable $data): array
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }
}
