<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Printer;

/**
 * Formatting policy used when rendering inserted or replaced nodes.
 */
class PrintPolicy
{
    public function __construct(
        public readonly string $indent = '    ',
        public readonly string $newline = "\n",
    ) {
    }
}
