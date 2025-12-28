<?php

use App\Http\Middleware\CanBook;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureOwner;
use App\Http\Middleware\IsTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
     $middleware->alias([
        'isOwner'=>EnsureOwner::class,
        'isTenant'=>IsTenant::class,
        'isAdmin'=>EnsureAdmin::class,
        'canbook'=>CanBook::class
     ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
