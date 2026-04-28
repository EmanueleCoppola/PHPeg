<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Expression;

use EmanueleCoppola\PHPPeg\Ast\AstNode;
use EmanueleCoppola\PHPPeg\Parser\ParseContext;
use EmanueleCoppola\PHPPeg\Result\MatchResult;

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

    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        $nodes = [];
        $cursor = $offset;

        foreach ($this->expressions as $expression) {
            $result = $expression->match($context, $cursor);
            if ($result === null) {
                return null;
            }

            $cursor = $result->endOffset();
            array_push($nodes, ...$result->nodes());
        }

        return new MatchResult($offset, $cursor, $nodes);
    }

    public function describe(): string
    {
        return 'sequence';
    }
}
