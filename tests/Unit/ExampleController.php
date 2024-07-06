<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Tests\Unit;

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

    public function edit(): JsonResponse
    {
        return $this->json->response($this->someUser);
    }
}
