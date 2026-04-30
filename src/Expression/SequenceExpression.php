<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Expression;

use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Matches a sequence of expressions in order.
 */
class SequenceExpression extends AbstractExpression
{
    /**
     * @param list<ExpressionInterface> $expressions
     */
    public function __construct(
        private readonly array $expressions,
    ) {
    }

    /**
     * @return list<ExpressionInterface>
     */
    public function expressions(): array
    {
        return $this->expressions;
    }

    /**
     * @inheritDoc
     */
    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        $nodes = [];
        $cursor = $offset;

        foreach ($this->expressions as $expression) {
            $result = $context->matchExpression($expression, $cursor);
            if ($result === null) {
                return null;
            }

            $cursor = $result->endOffset();
            foreach ($result->nodes() as $node) {
                $nodes[] = $node;
            }
        }

        return new MatchResult($offset, $cursor, $nodes);
    }

    /**
     * @inheritDoc
     */
    public function describe(): string
    {
        return 'sequence';
    }
}
