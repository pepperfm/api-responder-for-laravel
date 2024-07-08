<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder;

use Pepperfm\ApiBaseResponder\Attributes\ResponseDataKey;

final readonly class ValidateRestMethod
{
    public static function make(): self
    {
        return new self();
    }

    public function getDataKey(\ReflectionMethod $callerFunction): string
    {
        $methodsForSingularKey = config('laravel-api-responder.methods_for_singular_key', ['show', 'update']);

        /** @var \ReflectionAttribute|null $attribute */
        $attribute = collect($callerFunction->getAttributes())->filter(
            static fn(\ReflectionAttribute $item) => $item->getName() === ResponseDataKey::class
        )->first();

        /** @var ResponseDataKey|null $customDataKey */
        $customDataKey = $attribute?->newInstance();
        if ($customDataKey?->key()) {
            return $customDataKey->key();
        }
        if ($this->usingForRest() && in_array($callerFunction->getName(), $methodsForSingularKey, true)) {
            return config('laravel-api-responder.singular_data_key', 'entity');
        }

        return config('laravel-api-responder.plural_data_key', 'entities');
    }

    private function usingForRest(): bool
    {
        return config('laravel-api-responder.using_for_rest', true);
    }
}
