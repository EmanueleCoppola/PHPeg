<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Expression;

use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Matches an exact literal string.
 */
class LiteralExpression extends AbstractExpression
{
    private readonly int $length;

    private readonly string $description;

    public function __construct(
        private readonly string $literal,
    ) {
        $this->length = strlen($literal);
        $this->description = sprintf('"%s"', $literal);
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
        if (substr_compare($context->input()->text(), $this->literal, $offset, $this->length) !== 0) {
            $context->recordFailure($offset, $this->describe());

            return null;
        }

        return new MatchResult($offset, $offset + $this->length);
    }

    public function describe(): string
    {
        return $this->description;
    }
}
