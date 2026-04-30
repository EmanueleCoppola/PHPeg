<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Expression;

use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Matches an optional expression.
 */
class OptionalExpression extends AbstractExpression
{
    /**
     * Initializes a new OptionalExpression instance.
     */
    public function __construct(
        private readonly ExpressionInterface $expression,
    ) {
    }

    /**
     * Returns the wrapped operand.
     */
    public function expression(): ExpressionInterface
    {
        return $this->expression;
    }

    /**
     * @inheritDoc
     */
    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        return $context->matchExpression($this->expression, $offset) ?? $context->emptyMatch($offset);
    }

    /**
     * @inheritDoc
     */
    public function describe(): string
    {
        return $this->expression->describe() . '?';
    }
}
