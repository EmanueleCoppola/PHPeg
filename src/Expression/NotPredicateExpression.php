<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Expression;

use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

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
        if ($context->matchExpressionSilently($this->expression, $offset) !== null) {
            $context->recordFailure($offset, $this->describe());

            return null;
        }

        return $context->emptyMatch($offset);
    }

    public function describe(): string
    {
        return '!' . $this->expression->describe();
    }
}
