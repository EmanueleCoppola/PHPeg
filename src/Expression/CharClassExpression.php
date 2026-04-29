<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Expression;

use InvalidArgumentException;
use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Matches a single character using a PEG character class.
 */
class CharClassExpression extends AbstractExpression
{
    private readonly string $regex;

    public function __construct(
        private readonly string $pattern,
    ) {
        if (!preg_match('~^\[(?:\\\\.|[^\]])+\]$~', $pattern)) {
            throw new InvalidArgumentException(sprintf('Invalid character class pattern: %s', $pattern));
        }

        $this->regex = sprintf('~\G%s~Au', $pattern);
    }

    /**
     * Returns the original character class pattern.
     */
    public function pattern(): string
    {
        return $this->pattern;
    }

    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        if (preg_match($this->regex, $context->input()->text(), $matches, 0, $offset) !== 1) {
            $context->recordFailure($offset, $this->describe());

            return null;
        }

        return new MatchResult($offset, $offset + strlen($matches[0]));
    }

    public function describe(): string
    {
        return $this->pattern;
    }
}
