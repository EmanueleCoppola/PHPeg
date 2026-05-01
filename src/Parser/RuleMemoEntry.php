<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Parser;

use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Stores the cached state for a rule match at a specific offset.
 */
class RuleMemoEntry
{
    /**
     * Initializes a new RuleMemoEntry instance.
     */
    public function __construct(
        private ?MatchResult $result = null,
        private bool $evaluating = true,
        private bool $leftRecursive = false,
    ) {
    }

    /**
     * Returns the cached match result, if any.
     */
    public function result(): ?MatchResult
    {
        return $this->result;
    }

    /**
     * Updates the cached match result.
     */
    public function setResult(?MatchResult $result): void
    {
        $this->result = $result;
    }

    /**
     * Returns whether the rule is currently being evaluated.
     */
    public function isEvaluating(): bool
    {
        return $this->evaluating;
    }

    /**
     * Marks the rule as currently being evaluated.
     */
    public function beginEvaluation(): void
    {
        $this->evaluating = true;
    }

    /**
     * Marks the rule as finished evaluating.
     */
    public function finishEvaluation(): void
    {
        $this->evaluating = false;
    }

    /**
     * Marks the cached match as part of a left-recursive cycle.
     */
    public function markLeftRecursive(): void
    {
        $this->leftRecursive = true;
    }

    /**
     * Returns whether the entry encountered left recursion.
     */
    public function hasLeftRecursion(): bool
    {
        return $this->leftRecursive;
    }
}
