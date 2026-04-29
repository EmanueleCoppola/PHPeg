<?php

declare(strict_types=1);

use EmanueleCoppola\PHPeg\App\Console\Kernel;
use EmanueleCoppola\PHPeg\App\Exceptions\Handler;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use LaravelZero\Framework\Application;

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
*/

$app = new Application(dirname(__DIR__, 2));
$app->useBootstrapPath(__DIR__);
$app->useConfigPath(dirname(__DIR__) . '/config');

$app->singleton(
    ConsoleKernelContract::class,
    Kernel::class,
);

$app->singleton(
    ExceptionHandler::class,
    Handler::class,
);

return $app;
