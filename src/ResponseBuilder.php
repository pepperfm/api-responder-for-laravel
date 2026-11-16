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

    private ?string $methodName = null;

    private bool $withoutWrapping = false;

    public static function make(): self
    {
        return new self();
    }

    public function withDataKey(string $key): self
    {
        if ($key === '' || in_array($key, ['meta', 'message'], true)) {
            throw new InvalidArgumentException('The data key must be nonempty and cannot be meta or message.');
        }

        $this->dataKey = $key;

        return $this;
    }

    public function forMethod(string $methodName): self
    {
        $this->methodName = $methodName;

        return $this;
    }

    /**
     * Read attributes on a controller action, optionally inferred from the caller.
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

                if ($candidateClass === self::class ||
                    $candidateClass === ApiBaseResponder::class ||
                    is_a($candidateClass, Facade::class, true)) {
                    continue;
                }

                $class ??= $candidateClass;
                $method ??= $candidateMethod;
                break;
            }
        }

        if ($class === null || $method === null) {
            throw new LogicException('Unable to determine action. Pass class and method explicitly to fromAction().');
        }

        $action = new ReflectionMethod($class, $method);
        $this->methodName = $method;

        if ($attribute = ($action->getAttributes(ResponseDataKey::class)[0] ?? null)) {
            $this->withDataKey($attribute->newInstance()->key());
        }

        if ($action->getAttributes(WithoutWrapping::class) !== []) {
            $this->withoutWrapping = true;
        }

        return $this;
    }

    public function withoutWrapping(): self
    {
        $this->withoutWrapping = true;

        return $this;
    }

    public function response(
        array|Arrayable $data,
        array $meta = [],
        string $message = 'Success',
        int $httpStatusCode = JsonResponse::HTTP_OK,
    ): JsonResponse {
        $payload = self::asArray($data);

        if ($this->shouldDisableWrapping()) {
            if ($payload !== [] && array_is_list($payload)) {
                throw new InvalidArgumentException('Unwrapped responses need associative data; use raw() for lists.');
            }

            if (array_key_exists('meta', $payload) || array_key_exists('message', $payload)) {
                throw new InvalidArgumentException('Unwrapped data cannot contain meta or message; use raw() instead.');
            }
        } else {
            $key = $this->resolveDataKey();

            if ($key === '' || in_array($key, ['meta', 'message'], true)) {
                throw new InvalidArgumentException('Configured data key cannot be empty, meta or message.');
            }

            $payload = [$key => $payload];
        }

        return response()->json([
            ...$payload,
            'meta' => $meta,
            'message' => $message,
        ], $httpStatusCode, [], JSON_UNESCAPED_UNICODE);
    }

    public function raw(
        array|Arrayable $data,
        int $httpStatusCode = JsonResponse::HTTP_OK,
    ): JsonResponse {
        return response()->json(self::asArray($data), $httpStatusCode, [], JSON_UNESCAPED_UNICODE);
    }

    public function stored(
        array|Arrayable $data = [],
        array $meta = [],
        string $message = 'Stored',
    ): JsonResponse {
        return $this->response($data, $meta, $message, JsonResponse::HTTP_CREATED);
    }

    public function deleted(): JsonResponse
    {
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
        return $this->withoutWrapping || config('laravel-api-responder.without_wrapping', false);
    }

    private static function asArray(array|Arrayable $data): array
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }
}
