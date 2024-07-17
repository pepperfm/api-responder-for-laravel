<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pepperfm\ApiBaseResponder\Tests\Fixtures\ExampleController;

Route::get('api/users', [ExampleController::class, 'index'])->name('api.users.index');
