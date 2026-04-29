<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Error;

/**
 * Represents a parse failure with source position information.
 */
class ParseError
{
    /**
     * @param list<string> $expected
     */
    public function __construct(
        private readonly int $offset,
        private readonly int $line,
        private readonly int $column,
        private readonly array $expected,
        private readonly string $snippet,
        private readonly ?string $customMessage = null,
    ) {
    }

    /**
     * Creates a parse error for unsupported left recursion.
     */
    public static function leftRecursion(
        string $ruleName,
        int $offset,
        int $line,
        int $column,
        string $snippet,
    ): self {
        return new self(
            $offset,
            $line,
            $column,
            [],
            $snippet,
            sprintf(
                'Left-recursive rule detected: %s at line %d, column %d (offset %d). Near: %s',
                $ruleName,
                $line,
                $column,
                $offset,
                $snippet,
            ),
        );
    }

    /**
     * Returns the failing byte offset.
     */
    public function offset(): int
    {
        return $this->offset;
    }

    /**
     * Returns the 1-based line number.
     */
    public function line(): int
    {
        return $this->line;
    }

    /**
     * Returns the 1-based column number.
     */
    public function column(): int
    {
        return $this->column;
    }

    /**
     * @return list<string>
     */
    public function expected(): array
    {
        return $this->expected;
    }

    /**
     * Returns a short excerpt around the failing location.
     */
    public function snippet(): string
    {
        return $this->snippet;
    }

    /**
     * Returns a human-readable parse error message.
     */
    public function message(): string
    {
        if ($this->customMessage !== null) {
            return $this->customMessage;
        }

        $expected = $this->expected === [] ? 'unknown input' : implode(', ', $this->expected);

        return sprintf(
            'Parse error at line %d, column %d (offset %d). Expected: %s. Near: %s',
            $this->line,
            $this->column,
            $this->offset,
            $expected,
            $this->snippet,
        );
    }
}
