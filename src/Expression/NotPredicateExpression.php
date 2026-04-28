<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Expression;

use EmanueleCoppola\PHPPeg\Parser\ParseContext;
use EmanueleCoppola\PHPPeg\Result\MatchResult;

/**
 * Negative lookahead predicate.
 */
class NotPredicateExpression extends AbstractExpression
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
        if ($this->expression->match($context, $offset) !== null) {
            $context->recordFailure($offset, $this->describe());

            return null;
        }

        return MatchResult::empty($offset);
    }

    public function describe(): string
    {
        return '!' . $this->expression->describe();
    }
}
