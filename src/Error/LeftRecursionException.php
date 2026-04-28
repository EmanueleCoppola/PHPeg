<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Error;

use RuntimeException;

/**
 * Raised when the parser detects unsupported left recursion.
 */
class LeftRecursionException extends RuntimeException
{
    public function __construct(
        private readonly string $ruleName,
        private readonly int $offset,
    ) {
        parent::__construct(sprintf('Left-recursive rule detected: %s', $ruleName));
    }

    /**
     * Returns the rule involved in the left-recursive cycle.
     */
    public function ruleName(): string
    {
        return $this->ruleName;
    }

    /**
     * Returns the input offset where the cycle was detected.
     */
    public function offset(): int
    {
        return $this->offset;
    }
}
