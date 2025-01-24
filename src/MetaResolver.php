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

    public function __invoke(): array
    {
        $data = [];
        $meta = [];
        if (
            (is_array($this->data) || $this->data instanceof Collection) &&
            (is_array($this->meta) || $this->meta instanceof Collection)
        ) {
            $data = $this->data;
            $meta = $this->meta;
        }
        if (
            ($this->data instanceof LengthAwarePaginator || $this->data instanceof CursorPaginator) &&
            (is_array($this->meta) || $this->meta instanceof Collection)
        ) {
            $data = $this->data->getCollection();
            $meta = array_merge($this->meta, paginate($this->data));
        }
        if (
            (is_array($this->data) || $this->data instanceof Collection) &&
            ($this->meta instanceof LengthAwarePaginator || $this->meta instanceof CursorPaginator)
        ) {
            $data = $this->data;
            $meta = paginate($this->meta);
        }

        return [
            'data' => $data,
            'meta' => $meta,
        ];
    }
}
