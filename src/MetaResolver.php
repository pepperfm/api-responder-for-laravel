<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder;

use Illuminate\Pagination\LengthAwarePaginator;

final readonly class MetaResolver
{
    public function __construct(private array|LengthAwarePaginator $data, private array|LengthAwarePaginator $meta)
    {
    }

    public function __invoke(): array
    {
        $data = [];
        $meta = [];
        if (is_array($this->data) && is_array($this->meta)) {
            $data = $this->data;
            $meta = $this->meta;
        }
        if ($this->data instanceof LengthAwarePaginator && is_array($this->meta)) {
            $data = $this->data->getCollection();
            $meta = array_merge($this->meta, [
                'pagination' => paginate($this->data),
            ]);
        }
        if (is_array($this->data) && $this->meta instanceof LengthAwarePaginator) {
            $data = $this->data;
            $meta = [
                'pagination' => paginate($this->meta),
            ];
        }

        return [
            'data' => $data,
            'meta' => $meta,
        ];
    }
}
