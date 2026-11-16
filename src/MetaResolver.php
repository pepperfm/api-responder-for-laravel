<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

final readonly class MetaResolver
{
    public function __construct(
        private array|Arrayable|Paginator|LengthAwarePaginator|CursorPaginator $data,
        private array|Arrayable|Paginator|LengthAwarePaginator|CursorPaginator $meta,
    ) {
    }

    /**
     * @return array{data: array|Arrayable, meta: array}
     */
    public function __invoke(): array
    {
        return self::resolve($this->data, $this->meta);
    }

    /**
     * @return array{data: array|Arrayable, meta: array}
     */
    public static function resolve(
        array|Arrayable|Paginator|LengthAwarePaginator|CursorPaginator $data,
        array|Arrayable|Paginator|LengthAwarePaginator|CursorPaginator $meta,
    ): array {
        if (self::isPaginator($data)) {
            $metaArray = self::isPaginator($meta) ? [] : self::toArray($meta);

            return [
                'data' => $data->getCollection()->toArray(),
                'meta' => [
                    ...$metaArray,
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

        return [
            'data' => $data,
            'meta' => self::toArray($meta),
        ];
    }

    public static function extractPagination(Paginator|LengthAwarePaginator|CursorPaginator $paginator): array
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

        $pagination = [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'next_page_url' => $paginator->nextPageUrl(),
        ];

        if ($paginator instanceof LengthAwarePaginator) {
            return [
                ...$pagination,
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'links' => $paginator->linkCollection()->toArray(),
            ];
        }

        return $pagination;
    }

    private static function isPaginator(mixed $value): bool
    {
        return $value instanceof Paginator ||
            $value instanceof LengthAwarePaginator ||
            $value instanceof CursorPaginator;
    }

    private static function toArray(array|Arrayable $data): array
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }
}
