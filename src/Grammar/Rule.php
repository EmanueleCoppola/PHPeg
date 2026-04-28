<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Grammar;

use EmanueleCoppola\PHPPeg\Ast\AstNode;
use EmanueleCoppola\PHPPeg\Expression\ExpressionInterface;
use EmanueleCoppola\PHPPeg\Parser\ParseContext;
use EmanueleCoppola\PHPPeg\Result\MatchResult;

/**
 * Represents a named grammar rule.
 */
class Rule
{
    public function __construct(
        private readonly string $name,
        private readonly ExpressionInterface $expression,
    ) {
    }

    /**
     * Returns the rule name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the rule body.
     */
    public function expression(): ExpressionInterface
    {
        return $this->expression;
    }

    /**
     * Matches this rule and wraps the resulting subtree into an AST node.
     */
    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        $result = $this->expression->match($context, $offset);
        if ($result === null) {
            return null;
        }

        $node = new AstNode(
            $this->name,
            $context->input()->slice($offset, $result->endOffset()),
            $offset,
            $result->endOffset(),
            $result->nodes(),
        );

        return new MatchResult($offset, $result->endOffset(), [$node]);
    }
}
