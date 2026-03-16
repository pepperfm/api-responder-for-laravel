<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

final readonly class MetaResolver
{
    public function __construct(
        private array|Collection|LengthAwarePaginator|CursorPaginator $data,
        private array|Collection|LengthAwarePaginator|CursorPaginator $meta
    ) {
    }

    /**
     * Resolve data and meta from mixed input types.
     *
     * @return array{data: mixed, meta: array}
     */
    public function __invoke(): array
    {
        return self::resolve($this->data, $this->meta);
    }

    /**
     * Static resolver — preferred entry point.
     *
     * @param array|Collection|CursorPaginator|LengthAwarePaginator $data
     * @param array|Collection|CursorPaginator|LengthAwarePaginator $meta
     *
     * @return array{data: mixed, meta: array}
     */
    public static function resolve(
        array|Collection|LengthAwarePaginator|CursorPaginator $data,
        array|Collection|LengthAwarePaginator|CursorPaginator $meta
    ): array {
        if (
            ($data instanceof LengthAwarePaginator || $data instanceof CursorPaginator) &&
            (is_array($meta) || $meta instanceof Collection)
        ) {
            return [
                'data' => $data->getCollection(),
                'meta' => array_merge(
                    $meta instanceof Collection ? $meta->toArray() : $meta,
                    ['pagination' => self::extractPagination($data)]
                ),
            ];
        }

        if (
            (is_array($data) || $data instanceof Collection) &&
            ($meta instanceof LengthAwarePaginator || $meta instanceof CursorPaginator)
        ) {
            return [
                'data' => $data,
                'meta' => ['pagination' => self::extractPagination($meta)],
            ];
        }

        return [
            'data' => $data,
            'meta' => $meta instanceof Collection ? $meta->toArray() : (is_array($meta) ? $meta : []),
        ];
    }

    /**
     * Extract pagination metadata from a paginator instance.
     *
     * @param CursorPaginator|LengthAwarePaginator $paginator
     *
     * @return array
     */
    public static function extractPagination(LengthAwarePaginator|CursorPaginator $paginator): array
    {
        if ($paginator instanceof CursorPaginator) {
            return [
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ];
        }

        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'next_page_url' => $paginator->nextPageUrl(),
            'links' => $paginator->linkCollection(),
        ];
    }
}
