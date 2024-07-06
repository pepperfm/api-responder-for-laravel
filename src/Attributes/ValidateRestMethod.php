<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
final readonly class ValidateRestMethod
{
    public function getDataKey(?string $callerFunctionName = null): string
    {
        $methodsForSingularKey = config('laravel-api-responder.methods_for_singular_key');
        if ($this->usingForRest() && in_array($callerFunctionName, $methodsForSingularKey, true)) {
            return 'entity';
        }

        return 'entities';
    }

    private function usingForRest(): bool
    {
        return config('laravel-api-responder.using_for_rest', true);
    }
}
