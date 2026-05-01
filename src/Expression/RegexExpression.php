<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Expression;

use InvalidArgumentException;
use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Matches an anchored PCRE pattern.
 */
class RegexExpression extends AbstractExpression
{
    private readonly string $regex;

    private readonly string $description;

    private readonly bool $canMatchEmpty;

    /**
     * Initializes a new RegexExpression instance.
     */
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
        $this->description = sprintf('regex(%s)', $pattern);
        $this->canMatchEmpty = preg_match($this->regex, '') === 1;
    }

    /**
     * Returns the original PCRE pattern.
     */
    public function pattern(): string
    {
        return $this->pattern;
    }

    /**
     * Returns whether the regex can match the empty string.
     */
    public function canMatchEmpty(): bool
    {
        return $this->canMatchEmpty;
    }

    /**
     * @inheritDoc
     */
    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        if (preg_match($this->regex, $context->input()->text(), $matches, 0, $offset) !== 1) {
            $context->recordFailure($offset, $this->describe());

            return null;
        }

        return new MatchResult($offset, $offset + strlen($matches[0]));
    }

    /**
     * @inheritDoc
     */
    public function describe(): string
    {
        return $this->description;
    }
}
