<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Expression;

use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Matches another rule by name.
 */
class RuleReferenceExpression extends AbstractExpression
{
    /**
     * Initializes a new RuleReferenceExpression instance.
     */
    public function __construct(
        private readonly string $ruleName,
    ) {
    }

    /**
     * Returns the referenced rule name.
     */
    public function ruleName(): string
    {
        return $this->ruleName;
    }

    /**
     * @inheritDoc
     */
    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        return $context->matchRule($this->ruleName, $offset);
    }

    /**
     * @inheritDoc
     */
    public function describe(): string
    {
        return sprintf('<%s>', $this->ruleName);
    }
}
