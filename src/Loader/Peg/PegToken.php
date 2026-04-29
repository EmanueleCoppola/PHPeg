<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Loader\Peg;

/**
 * Represents a token emitted while lexing PEG grammar text.
 */
class PegToken
{
    public function __construct(
        public readonly string $type,
        public readonly string $lexeme,
        public readonly int $offset,
    ) {
    }
}
