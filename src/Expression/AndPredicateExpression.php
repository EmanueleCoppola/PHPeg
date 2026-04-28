<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Expression;

use EmanueleCoppola\PHPPeg\Parser\ParseContext;
use EmanueleCoppola\PHPPeg\Result\MatchResult;

/**
 * Positive lookahead predicate.
 */
class AndPredicateExpression extends AbstractExpression
{
    public function __construct(
        private readonly ExpressionInterface $expression,
    ) {
    }

    /**
     * Returns the looked-ahead operand.
     */
    public function expression(): ExpressionInterface
    {
        return $this->expression;
    }

    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        return $this->expression->match($context, $offset) !== null ? MatchResult::empty($offset) : null;
    }

    public function describe(): string
    {
        return '&' . $this->expression->describe();
    }
}
