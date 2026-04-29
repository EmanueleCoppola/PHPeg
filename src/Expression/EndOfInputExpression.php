<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Expression;

use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Matches only when the parser is at end of input.
 */
class EndOfInputExpression extends AbstractExpression
{
    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        if ($offset !== $context->input()->length()) {
            $context->recordFailure($offset, $this->describe());

            return null;
        }

        return $context->emptyMatch($offset);
    }

    public function describe(): string
    {
        return 'EOF';
    }
}
