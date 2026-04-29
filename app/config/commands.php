<?php

declare(strict_types=1);

return [
    'default' => null,
    'paths' => [],
    'add' => [
        EmanueleCoppola\PHPeg\App\Commands\BenchmarkCommand::class,
        EmanueleCoppola\PHPeg\App\Commands\BenchmarkCompareCommand::class,
    ],
    'hidden' => [],
    'remove' => [],
];
