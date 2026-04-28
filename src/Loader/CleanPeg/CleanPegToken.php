<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Loader\CleanPeg;

/**
 * Represents a token in CleanPeg grammar source.
 */
class CleanPegToken
{
    public function __construct(
        public readonly string $type,
        public readonly string $lexeme,
        public readonly int $line,
        public readonly int $column,
    ) {
    }
}
