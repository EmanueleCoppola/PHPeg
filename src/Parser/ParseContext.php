<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Parser;

use EmanueleCoppola\PHPeg\Error\ParseError;
use EmanueleCoppola\PHPeg\Error\LeftRecursionException;
use EmanueleCoppola\PHPeg\Grammar\Grammar;
use EmanueleCoppola\PHPeg\Result\MatchResult;

/**
 * Holds parser state, memoization, and failure diagnostics.
 */
class ParseContext
{
    /**
     * @var array<string, array<int, MatchResult|null>>
     */
    private array $memo = [];

    private int $furthestOffset = 0;

    /**
     * @var array<string, true>
     */
    private array $expected = [];

    /**
     * @var array<string, array<int, true>>
     */
    private array $activeRules = [];

    public function __construct(
        private readonly Grammar $grammar,
        private readonly InputBuffer $input,
    ) {
    }

    /**
     * Returns the active grammar.
     */
    public function grammar(): Grammar
    {
        return $this->grammar;
    }

    /**
     * Returns the input buffer.
     */
    public function input(): InputBuffer
    {
        return $this->input;
    }

    /**
     * Matches a named rule with memoization.
     */
    public function matchRule(string $ruleName, int $offset): ?MatchResult
    {
        $rule = $this->grammar->rule($ruleName);
        if ($rule === null) {
            $this->recordFailure($offset, sprintf('rule <%s>', $ruleName));

            return null;
        }

        if (array_key_exists($offset, $this->memo[$ruleName] ?? [])) {
            return $this->memo[$ruleName][$offset];
        }

        if (($this->activeRules[$ruleName][$offset] ?? false) === true) {
            throw new LeftRecursionException($ruleName, $offset);
        }

        $this->activeRules[$ruleName][$offset] = true;

        try {
            $this->memo[$ruleName][$offset] = $rule->match($this, $offset);
        } finally {
            unset($this->activeRules[$ruleName][$offset]);
            if ($this->activeRules[$ruleName] === []) {
                unset($this->activeRules[$ruleName]);
            }
        }

        return $this->memo[$ruleName][$offset];
    }

    /**
     * Records an expected token description at a failing offset.
     */
    public function recordFailure(int $offset, string $expected): void
    {
        if ($offset > $this->furthestOffset) {
            $this->furthestOffset = $offset;
            $this->expected = [$expected => true];

            return;
        }

        if ($offset === $this->furthestOffset) {
            $this->expected[$expected] = true;
        }
    }

    /**
     * Builds the final parse error.
     */
    public function error(): ParseError
    {
        $position = $this->input->lineAndColumn($this->furthestOffset);

        return new ParseError(
            $this->furthestOffset,
            $position['line'],
            $position['column'],
            array_keys($this->expected),
            $this->input->snippet($this->furthestOffset),
        );
    }
}
