<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Expression;

use InvalidArgumentException;
use EmanueleCoppola\PHPPeg\Parser\ParseContext;
use EmanueleCoppola\PHPPeg\Result\MatchResult;

/**
 * Matches an anchored PCRE pattern.
 */
class RegexExpression extends AbstractExpression
{
    private readonly string $regex;

    public function __construct(
        private readonly string $pattern,
    ) {
        $regex = sprintf('~\G(?:%s)~Au', $pattern);
        set_error_handler(static fn (): bool => true);
        $isValid = preg_match($regex, '') !== false;
        restore_error_handler();

        if (!$isValid) {
            throw new InvalidArgumentException(sprintf('Invalid regex pattern: %s', $pattern));
        }

        $this->regex = $regex;
    }

    /**
     * Returns the original PCRE pattern.
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
        return sprintf('regex(%s)', $this->pattern);
    }
}
