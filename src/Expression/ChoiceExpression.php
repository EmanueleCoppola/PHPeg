<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Expression;

use EmanueleCoppola\PHPPeg\Parser\ParseContext;
use EmanueleCoppola\PHPPeg\Result\MatchResult;

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

    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        foreach ($this->alternatives as $alternative) {
            $result = $alternative->match($context, $offset);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    public function describe(): string
    {
        return 'choice';
    }
}
