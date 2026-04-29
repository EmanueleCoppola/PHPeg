<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Error;

use InvalidArgumentException;

/**
 * Raised when a grammar declaration syntax is invalid.
 */
class GrammarSyntaxError extends InvalidArgumentException
{
    public function __construct(
        private readonly string $grammarKind,
        private readonly int $syntaxLine,
        private readonly int $syntaxColumn,
        string $message,
    ) {
        parent::__construct(
            sprintf(
                'Invalid %s syntax at line %d, column %d: %s',
                $grammarKind,
                $syntaxLine,
                $syntaxColumn,
                $message,
            ),
        );
    }

    /**
     * Returns the grammar syntax kind, such as CleanPeg.
     */
    public function grammarKind(): string
    {
        return $this->grammarKind;
    }

    /**
     * Returns the 1-based line number.
     */
    public function line(): int
    {
        return $this->syntaxLine;
    }

    /**
     * Returns the 1-based column number.
     */
    public function column(): int
    {
        return $this->syntaxColumn;
    }
}
