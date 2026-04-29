<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Expression;

use EmanueleCoppola\PHPeg\Parser\ParseContext;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Matches a single character.
 */
class AnyCharacterExpression extends AbstractExpression
{
    public function match(ParseContext $context, int $offset): ?MatchResult
    {
        $char = $context->input()->charAt($offset);
        if ($char === null) {
            $context->recordFailure($offset, $this->describe());

            return null;
        }

        return new MatchResult($offset, $offset + 1);
    }

    public function describe(): string
    {
        return '.';
    }
}
