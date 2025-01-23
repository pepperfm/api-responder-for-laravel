<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder;

use Pepperfm\ApiBaseResponder\Attributes\WithoutWrapping;

final readonly class FormatByWrappingOption
{
    public static function make(): self
    {
        return new self();
    }
    
    public function format(\ReflectionMethod $callerFunction): bool
    {
        /** @var \ReflectionAttribute|null $attribute */
        $attribute = collect($callerFunction->getAttributes())->filter(
            static fn(\ReflectionAttribute $item) => $item->getName() === WithoutWrapping::class
        )->first();
        
        return $attribute !== null || config('laravel-api-responder.without_wrapping', false);
    }
}
