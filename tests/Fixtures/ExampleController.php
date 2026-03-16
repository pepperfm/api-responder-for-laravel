<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Tests\Fixtures;

use Illuminate\Http\JsonResponse;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

class ExampleController
{
    public array $someUser;

    public function __construct(public ResponseContract $json)
    {
        $this->someUser = [
            'id' => fake()->uuid(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
        ];
    }

    public function index(): JsonResponse
    {
        return $this->json->forMethod('index')->response($this->someUser);
    }

    public function omg(): JsonResponse
    {
        return $this->json->forMethod('omg')->response($this->someUser);
    }

    public function show(): JsonResponse
    {
        return $this->json->forMethod('show')->response($this->someUser);
    }

    public function update(): JsonResponse
    {
        return $this->json->forMethod('update')->response($this->someUser);
    }

    public function attributeWithoutParam(): JsonResponse
    {
        return $this->json->withDataKey('entity')->response($this->someUser);
    }

    public function attributeWithParam(): JsonResponse
    {
        return $this->json->withDataKey('random_key')->response($this->someUser);
    }

    public function withoutWrapping(): JsonResponse
    {
        return $this->json->build()->withoutWrapping()->response($this->someUser);
    }
}
