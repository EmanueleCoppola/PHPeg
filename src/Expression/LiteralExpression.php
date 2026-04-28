<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Expression;

use EmanueleCoppola\PHPPeg\Parser\ParseContext;
use EmanueleCoppola\PHPPeg\Result\MatchResult;

/**
 * Matches an exact literal string.
 */
class LiteralExpression extends AbstractExpression
{
    public function __construct(
        private readonly string $literal,
    ) {
    }

    /**
     * Returns the literal text.
     */
    public function literal(): string
    {
        return $this->literal;
    }

    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        if (substr_compare($context->input()->text(), $this->literal, $offset, strlen($this->literal)) !== 0) {
            $context->recordFailure($offset, $this->describe());

            return null;
        }

        return new MatchResult($offset, $offset + strlen($this->literal));
    }

    public function describe(): string
    {
        return sprintf('"%s"', $this->literal);
    }
}
