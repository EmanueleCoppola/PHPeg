<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Lake;

use EmanueleCoppola\PHPeg\Expression\AnyCharacterExpression;
use EmanueleCoppola\PHPeg\Expression\CharClassExpression;
use EmanueleCoppola\PHPeg\Expression\EndOfInputExpression;
use EmanueleCoppola\PHPeg\Expression\ExpressionInterface;
use EmanueleCoppola\PHPeg\Expression\LiteralExpression;
use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Represents one continuation sequence that can stop a lake.
 */
class LakeStopSequence
{
    /**
     * @var list<ExpressionInterface>
     */
    private array $expressions;

    private string $signature;

    /**
     * @param list<ExpressionInterface> $expressions
     */
    public function __construct(array $expressions)
    {
        $this->expressions = array_values($expressions);
        $this->signature = $this->buildSignature($this->expressions);
    }

    /**
     * Returns the expressions that make up the stop sequence.
     *
     * @return list<ExpressionInterface>
     */
    public function expressions(): array
    {
        return $this->expressions;
    }

    /**
     * Returns the stable sequence signature.
     */
    public function signature(): string
    {
        return $this->signature;
    }

    /**
     * Returns the first expression in the continuation, or null when empty.
     */
    public function firstExpression(): ?ExpressionInterface
    {
        return $this->expressions[0] ?? null;
    }

    /**
     * Returns whether the sequence is empty.
     */
    public function isEmpty(): bool
    {
        return $this->expressions === [];
    }

    /**
     * Returns whether the sequence can begin at the given offset without a full match.
     */
    public function canStartAt(ParseContext $context, int $offset): bool
    {
        $first = $this->firstExpression();
        if ($first === null) {
            return true;
        }

        if ($first instanceof EndOfInputExpression) {
            return $offset === $context->input()->length();
        }

        if ($first instanceof LiteralExpression) {
            $literal = $first->literal();
            if ($offset + strlen($literal) > $context->input()->length()) {
                return false;
            }

            return substr_compare($context->input()->text(), $literal, $offset, strlen($literal)) === 0;
        }

        if ($first instanceof CharClassExpression) {
            $char = $context->input()->charAt($offset);
            if ($char === null) {
                return false;
            }

            return preg_match('~' . $first->pattern() . '~Au', $char) === 1;
        }

        if ($first instanceof AnyCharacterExpression) {
            return $context->input()->charAt($offset) !== null;
        }

        return true;
    }

    /**
     * Attempts to match the full sequence at the provided offset.
     */
    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        $cursor = $offset;

        foreach ($this->expressions as $expression) {
            $result = $context->matchExpressionSilently($expression, $cursor);
            if ($result === null) {
                return null;
            }

            $cursor = $result->endOffset();
        }

        return new MatchResult($offset, $cursor);
    }

    /**
     * Builds a stable signature for the expression list.
     *
     * @param list<ExpressionInterface> $expressions
     */
    private function buildSignature(array $expressions): string
    {
        return implode('|', array_map(static fn (ExpressionInterface $expression): string => sprintf('%s#%d', $expression::class, spl_object_id($expression)), $expressions));
    }
}
