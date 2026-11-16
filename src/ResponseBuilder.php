<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;
use InvalidArgumentException;
use LogicException;
use Pepperfm\ApiBaseResponder\Attributes\ResponseDataKey;
use Pepperfm\ApiBaseResponder\Attributes\WithoutWrapping;
use ReflectionMethod;

final class ResponseBuilder
{
    private ?string $dataKey = null;

    private bool $noWrapping = false;

    private ?string $methodName = null;

    public static function make(): self
    {
        return new self();
    }

    public function forMethod(string $methodName): self
    {
        $this->methodName = $methodName;

        return $this;
    }

    public function withDataKey(string $key): self
    {
        $this->dataKey = $key;

        return $this;
    }

    /**
     * Read action attributes, with optional explicit controller class and method.
     * Automatic caller detection is opt-in and supports facade calls.
     *
     * @param class-string|null $class
     */
    public function fromAction(?string $class = null, ?string $method = null): self
    {
        if ($class === null || $method === null) {
            foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12) as $frame) {
                $candidateClass = $frame['class'] ?? null;
                $candidateMethod = $frame['function'] ?? null;

                if ($candidateClass === null || $candidateMethod === null) {
                    continue;
                }

                if (in_array($candidateClass, [self::class, ApiBaseResponder::class], true) ||
                    is_a($candidateClass, Facade::class, true)) {
                    continue;
                }

                $class ??= $candidateClass;
                $method ??= $candidateMethod;
                break;
            }
        }

        if ($class === null || $method === null) {
            throw new LogicException('Cannot detect the controller action. Pass class and method to fromAction().');
        }

        $reflection = new ReflectionMethod($class, $method);
        $this->methodName = $method;

        $dataKeyAttributes = $reflection->getAttributes(ResponseDataKey::class);

        if ($dataKeyAttributes !== []) {
            $this->dataKey = $dataKeyAttributes[0]->newInstance()->key();
        }

        if ($reflection->getAttributes(WithoutWrapping::class) !== []) {
            $this->noWrapping = true;
        }

        return $this;
    }

    public function withoutWrapping(): self
    {
        $this->noWrapping = true;

        return $this;
    }

    public function response(
        array|Arrayable $data,
        array $meta = [],
        string $message = 'Success',
        int $httpStatusCode = JsonResponse::HTTP_OK,
    ): JsonResponse {
        return $this->buildJsonResponse($data, $meta, $message, $httpStatusCode);
    }

    public function raw(
        array|Arrayable $data,
        int $httpStatusCode = JsonResponse::HTTP_OK,
    ): JsonResponse {
        return response()->json($this->toArray($data), $httpStatusCode, [], JSON_UNESCAPED_UNICODE);
    }

    public function stored(
        array|Arrayable $data = [],
        array $meta = [],
        string $message = 'Stored',
    ): JsonResponse {
        return $this->buildJsonResponse($data, $meta, $message, JsonResponse::HTTP_CREATED);
    }

    /**
     * No Content must not carry a JSON body.
     * Arguments remain accepted for backward-compatible calls.
     */
    public function deleted(
        array|Arrayable $data = [],
        string $message = 'Deleted',
    ): JsonResponse {
        return response()->json(null, JsonResponse::HTTP_NO_CONTENT)->setContent('');
    }

    public function resolveDataKey(): string
    {
        if ($this->dataKey !== null) {
            return $this->dataKey;
        }

        if ($this->methodName !== null &&
            config('laravel-api-responder.using_for_rest', true) &&
            in_array(
                $this->methodName,
                config('laravel-api-responder.methods_for_singular_key', ['show', 'update']),
                true,
            )) {
            return config('laravel-api-responder.singular_data_key', 'entity');
        }

        return config('laravel-api-responder.plural_data_key', 'entities');
    }

    public function shouldDisableWrapping(): bool
    {
        return $this->noWrapping || config('laravel-api-responder.without_wrapping', false);
    }

    private function buildJsonResponse(
        array|Arrayable $data,
        array $meta,
        string $message,
        int $httpStatusCode,
    ): JsonResponse {
        $payload = $this->toArray($data);

        if ($this->shouldDisableWrapping()) {
            if ($payload !== [] && array_is_list($payload)) {
                throw new InvalidArgumentException('Unwrapped responses require associative data. Use raw() for lists.');
            }

            foreach (['meta', 'message'] as $reservedKey) {
                if (array_key_exists($reservedKey, $payload)) {
                    throw new InvalidArgumentException(
                        "Unwrapped response data contains reserved key '{$reservedKey}'. Use raw() to preserve it.",
                    );
                }
            }

            $formatted = $payload;
        } else {
            $formatted = [$this->resolveDataKey() => $payload];
        }

        return response()->json([
            ...$formatted,
            'meta' => $meta,
            'message' => $message,
        ], $httpStatusCode, [], JSON_UNESCAPED_UNICODE);
    }

    private function toArray(array|Arrayable $data): array
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }
}
