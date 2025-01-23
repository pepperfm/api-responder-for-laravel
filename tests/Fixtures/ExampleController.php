<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Tests\Fixtures;

use Illuminate\Http\JsonResponse;
use Pepperfm\ApiBaseResponder\Attributes\ResponseDataKey;
use Pepperfm\ApiBaseResponder\Attributes\WithoutWrapping;
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
        return $this->json->response($this->someUser);
    }

    public function omg(): JsonResponse
    {
        return $this->json->response($this->someUser);
    }

    public function show(): JsonResponse
    {
        return $this->json->response($this->someUser);
    }

    public function update(): JsonResponse
    {
        return $this->json->response($this->someUser);
    }

    #[ResponseDataKey]
    public function attributeWithoutParam(): JsonResponse
    {
        return $this->json->response($this->someUser);
    }

    #[ResponseDataKey('random_key')]
    public function attributeWithParam(): JsonResponse
    {
        return $this->json->response($this->someUser);
    }

    #[WithoutWrapping]
    public function withoutWrapping(): JsonResponse
    {
        return $this->json->response($this->someUser);
    }
}
