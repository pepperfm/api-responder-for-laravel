<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder;

use Illuminate\Http\JsonResponse;
use Pepperfm\ApiBaseResponder\Attributes\ResponseDataKey;
use Pepperfm\ApiBaseResponder\Attributes\WithoutWrapping;

/*
 * Fluent response builder with explicit data key and wrapping control.
 *
 * Usage:
 *   $this->json->forMethod('index')->response($data);
 *   $this->json->withDataKey('users')->response($data);
 *   $this->json->withoutWrapping()->response($data);
 */
final class ResponseBuilder
{
    /** @var array<string, string> */
    private array $headers = ['Content-Type' => 'application/json; charset=UTF-8', 'charset' => 'utf-8'];

    private ?string $dataKey = null;

    private bool $noWrapping = false;

    private ?string $methodName = null;

    public static function make(): self
    {
        return new self();
    }

    /**
     * Set explicit REST method name for data key resolution.
     *
     * @param string $methodName
     */
    public function forMethod(string $methodName): self
    {
        $this->methodName = $methodName;

        return $this;
    }

    /**
     * Set explicit data key (bypasses REST convention resolution).
     *
     * @param string $key
     */
    public function withDataKey(string $key): self
    {
        $this->dataKey = $key;

        return $this;
    }

    /**
     * Configure builder from PHP attributes on the calling controller method.
     * Reads #[ResponseDataKey] and #[WithoutWrapping] attributes.
     *
     * Usage:
     *   #[ResponseDataKey('user')]
     *   public function show(): JsonResponse {
     *       return $this->json->fromAction()->response($data);
     *   }
     *
     * @param class-string|null $class
     * @param string|null $method
     */
    public function fromAction(?string $class = null, ?string $method = null): self
    {
        if ($class === null || $method === null) {
            $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
            $class ??= $caller['class'];
            $method ??= $caller['function'];
        }

        $reflection = new \ReflectionMethod($class, $method);

        $this->methodName = $method;

        /** @var \ReflectionAttribute|null $dataKeyAttr */
        $dataKeyAttr = collect($reflection->getAttributes(ResponseDataKey::class))->first();
        if ($dataKeyAttr !== null) {
            /** @var ResponseDataKey $instance */
            $instance = $dataKeyAttr->newInstance();
            $this->dataKey = $instance->key();
        }

        /** @var \ReflectionAttribute|null $wrappingAttr */
        $wrappingAttr = collect($reflection->getAttributes(WithoutWrapping::class))->first();
        if ($wrappingAttr !== null) {
            $this->noWrapping = true;
        }

        return $this;
    }

    /*
     * Disable data-key wrapping (data spread directly into response root).
     */
    public function withoutWrapping(): self
    {
        $this->noWrapping = true;

        return $this;
    }

    /**
     * Build a success response.
     *
     * @param array $data
     * @param array $meta
     * @param string $message
     * @param int $httpStatusCode
     *
     * @return JsonResponse
     */
    public function response(
        array $data,
        array $meta = [],
        string $message = 'Success',
        int $httpStatusCode = JsonResponse::HTTP_OK,
    ): JsonResponse {
        return $this->buildJsonResponse($data, $meta, $message, $httpStatusCode);
    }

    /**
     * Build a "created" response (HTTP 201).
     *
     * @param array $data
     * @param array $meta
     * @param string $message
     *
     * @return JsonResponse
     */
    public function stored(
        array $data = [],
        array $meta = [],
        string $message = 'Stored',
    ): JsonResponse {
        return $this->buildJsonResponse($data, $meta, $message, JsonResponse::HTTP_CREATED);
    }

    /**
     * Build a "deleted" response (HTTP 204).
     *
     * @param array $data
     * @param string $message
     *
     * @return JsonResponse
     */
    public function deleted(
        array $data = [],
        string $message = 'Deleted',
    ): JsonResponse {
        return $this->buildJsonResponse($data, [], $message, JsonResponse::HTTP_NO_CONTENT);
    }

    /*
     * Resolve the data key based on explicit settings or REST conventions.
     */
    public function resolveDataKey(): string
    {
        if ($this->dataKey !== null) {
            return $this->dataKey;
        }
        if ($this->methodName !== null) {
            $methodsForSingularKey = config('laravel-api-responder.methods_for_singular_key', ['show', 'update']);
            $usingForRest = config('laravel-api-responder.using_for_rest', true);

            if ($usingForRest && in_array($this->methodName, $methodsForSingularKey, true)) {
                return config('laravel-api-responder.singular_data_key', 'entity');
            }
        }

        return config('laravel-api-responder.plural_data_key', 'entities');
    }

    /*
     * Check if wrapping should be disabled.
     */
    public function shouldDisableWrapping(): bool
    {
        return $this->noWrapping || config('laravel-api-responder.without_wrapping', false);
    }

    /**
     * Core JSON response builder.
     *
     * @param array $data
     * @param array $meta
     * @param string $message
     * @param int $httpStatusCode
     */
    private function buildJsonResponse(
        array $data,
        array $meta,
        string $message,
        int $httpStatusCode,
    ): JsonResponse {
        $formatted = $this->shouldDisableWrapping()
            ? $data
            : [$this->resolveDataKey() => $data];

        return response()->json([
            ...$formatted,
            'meta' => $meta,
            'message' => $message,
        ], $httpStatusCode, $this->headers, JSON_UNESCAPED_UNICODE);
    }
}
