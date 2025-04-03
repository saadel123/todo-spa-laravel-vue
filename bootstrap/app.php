<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Define middleware aliases for Sanctum's token ability checks.
        // This allows us to verify that an incoming request is authenticated
        // with a token that has been granted specific abilities.
        // $middleware->alias([
        //     'abilities' => CheckAbilities::class,
        //     'ability' => CheckForAnyAbility::class,
        // ]);

        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class;
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('reminders:send')->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
