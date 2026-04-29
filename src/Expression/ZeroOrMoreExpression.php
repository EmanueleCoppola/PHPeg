<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Expression;

use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Matches zero or more repetitions of an expression.
 */
class ZeroOrMoreExpression extends AbstractExpression
{
    public function __construct(
        private readonly ExpressionInterface $expression,
    ) {
    }

    /**
     * Returns the repeated operand.
     */
    public function expression(): ExpressionInterface
    {
        return $this->expression;
    }

    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        $nodes = [];
        $cursor = $offset;

        while (true) {
            $result = $this->expression->match($context, $cursor);
            if ($result === null || $result->endOffset() === $cursor) {
                break;
            }

            $cursor = $result->endOffset();
            foreach ($result->nodes() as $node) {
                $nodes[] = $node;
            }
        }

        return new MatchResult($offset, $cursor, $nodes);
    }

    public function describe(): string
    {
        return $this->expression->describe() . '*';
    }
}
