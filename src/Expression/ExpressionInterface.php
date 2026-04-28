<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Expression;

use EmanueleCoppola\PHPPeg\Parser\ParseContext;
use EmanueleCoppola\PHPPeg\Result\MatchResult;

/**
 * Contract for all PEG expressions.
 */
interface ExpressionInterface
{
    /**
     * Attempts to match the expression at the provided offset.
     */
    public function match(ParseContext $context, int $offset): ?MatchResult;

    /**
     * Returns a short human-readable description used in diagnostics.
     */
    public function describe(): string;
}
