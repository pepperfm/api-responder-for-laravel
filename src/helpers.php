<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\CursorPaginator;
use Pepperfm\ApiBaseResponder\MetaResolver;

if (!function_exists('paginate')) {
    /**
     * Extract pagination metadata from a paginator instance.
     *
     * @param CursorPaginator|LengthAwarePaginator $paginator
     */
    function paginate(LengthAwarePaginator|CursorPaginator $paginator): array
    {
        return MetaResolver::extractPagination($paginator);
    }
}
