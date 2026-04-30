<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Expression;

use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Matches the first successful alternative.
 */
class ChoiceExpression extends AbstractExpression
{
    /**
     * @param list<ExpressionInterface> $alternatives
     */
    public function __construct(
        private readonly array $alternatives,
    ) {
    }

    /**
     * @return list<ExpressionInterface>
     */
    public function alternatives(): array
    {
        return $this->alternatives;
    }

    /**
     * @inheritDoc
     */
    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        foreach ($this->alternatives as $alternative) {
            $result = $context->matchExpression($alternative, $offset);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function describe(): string
    {
        return 'choice';
    }
}
