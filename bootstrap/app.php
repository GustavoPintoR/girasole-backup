<?php

use App\Console\Commands\TestMailer;
use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'active.user' => CheckUserActive::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'hooks/*',
            'stripe/*',
            '/stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->withSchedule(function (Schedule $schedule) {
        // $schedule->call(new TestMailer)->daily();
        $schedule->command('girasole:temperature indoor')->daily();
        $schedule->command('girasole:temperature outdoor')->daily();
        $schedule->command('girasole:temperature office')->daily();
        $schedule->command('weather:forecast')->dailyAt('07:15')->withoutOverlapping();
        $schedule->command('forecast:clean-logs')->dailyAt('07:30')->withoutOverlapping();
    })->create();
