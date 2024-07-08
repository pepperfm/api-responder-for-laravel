<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
final readonly class ResponseDataKey
{
    public function __construct(public string $key = 'entity')
    {
    }

    public function key(): ?string
    {
        return $this->key;
    }
}
