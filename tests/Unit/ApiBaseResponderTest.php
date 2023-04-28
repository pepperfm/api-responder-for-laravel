<?php

declare(strict_types=1);

namespace Pepperfm\ApiBaseResponder\Tests\Unit;

use Mockery;
use Mockery\MockInterface;
use Illuminate\Http\JsonResponse;
use Pepperfm\ApiBaseResponder\Tests\TestCase;
use Pepperfm\ApiBaseResponder\ResponseContract;

class ApiBaseResponderTest extends TestCase
{
    /**
     * @test
     */
    public function it_provides_a_getter_for_calendarId()
    {
        $this->instance(
            ResponseContract::class,
            Mockery::mock(ResponseContract::class, function (MockInterface $mock) {
                $mock->shouldReceive('response')->once()->andReturn(app(JsonResponse::class));
                $mock->shouldReceive('error')->once()->andReturn(app(JsonResponse::class));
            })
        );

        app(ResponseContract::class)->response([]);
        app(ResponseContract::class)->error();
    }
}
