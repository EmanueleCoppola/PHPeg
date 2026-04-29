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

    /**
     * @var list<array{rule:string,offset:int}>
     */
    private array $memoOrder = [];

    private int $furthestOffset = 0;

    /**
     * @var array<string, true>
     */
    private array $expected = [];

    private ?string $optimizedExpected = null;

    /**
     * @var array<string, array<int, true>>
     */
    private array $activeRules = [];

    /**
     * @var array<int, MatchResult>
     */
    private array $emptyMatches = [];

    private readonly bool $memoizationEnabled;

    private readonly bool $optimizeErrors;

    private readonly bool $reuseEmptyMatches;

    private readonly ?int $maxCacheEntries;

    public function __construct(
        private readonly Grammar $grammar,
        private readonly InputBuffer $input,
        private readonly ParserOptions $options = new ParserOptions(),
    ) {
        $this->memoizationEnabled = $options->memoizationEnabled();
        $this->optimizeErrors = $options->optimizeErrors();
        $this->reuseEmptyMatches = $options->reuseEmptyMatches();
        $this->maxCacheEntries = $options->maxCacheEntries();
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
     * Returns the active parser options.
     */
    public function options(): ParserOptions
    {
        return $this->options;
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

        if (($this->activeRules[$ruleName][$offset] ?? false) === true) {
            throw new LeftRecursionException($ruleName, $offset);
        }

        if ($this->memoizationEnabled) {
            if (array_key_exists($offset, $this->memo[$ruleName] ?? [])) {
                return $this->memo[$ruleName][$offset];
            }
        }

        $this->activeRules[$ruleName][$offset] = true;

        try {
            $result = $rule->match($this, $offset);
        } finally {
            unset($this->activeRules[$ruleName][$offset]);
            if ($this->activeRules[$ruleName] === []) {
                unset($this->activeRules[$ruleName]);
            }
        }

        if ($this->memoizationEnabled) {
            $this->storeMemoizedResult($ruleName, $offset, $result);
        }

        return $result;
    }

    /**
     * Records an expected token description at a failing offset.
     */
    public function recordFailure(int $offset, string $expected): void
    {
        if ($offset > $this->furthestOffset) {
            $this->furthestOffset = $offset;
            if ($this->optimizeErrors) {
                $this->optimizedExpected = $expected;
                $this->expected = [];

                return;
            }

            $this->expected = [$expected => true];

            return;
        }

        if ($offset === $this->furthestOffset && $this->optimizeErrors) {
            $this->optimizedExpected ??= $expected;

            return;
        }

        if ($offset === $this->furthestOffset) {
            $this->expected[$expected] = true;
        }
    }

    /**
     * Returns a cached zero-width match at the given offset when enabled.
     */
    public function emptyMatch(int $offset): MatchResult
    {
        if (!$this->reuseEmptyMatches) {
            return MatchResult::empty($offset);
        }

        if (!isset($this->emptyMatches[$offset])) {
            $this->emptyMatches[$offset] = MatchResult::empty($offset);
        }

        return $this->emptyMatches[$offset];
    }

    /**
     * Builds the final parse error.
     */
    public function error(): ParseError
    {
        $position = $this->input->lineAndColumn($this->furthestOffset);
        $expected = $this->optimizeErrors
            ? ($this->optimizedExpected === null ? [] : [$this->optimizedExpected])
            : array_keys($this->expected);

        return new ParseError(
            $this->furthestOffset,
            $position['line'],
            $position['column'],
            $expected,
            $this->input->snippet($this->furthestOffset),
        );
    }

    /**
     * Stores a memoized rule result and applies the configured cache limit.
     */
    private function storeMemoizedResult(string $ruleName, int $offset, ?MatchResult $result): void
    {
        $this->memo[$ruleName][$offset] = $result;

        if ($this->maxCacheEntries === null) {
            return;
        }

        $this->memoOrder[] = ['rule' => $ruleName, 'offset' => $offset];

        while (count($this->memoOrder) > $this->maxCacheEntries) {
            $entry = array_shift($this->memoOrder);
            if ($entry === null) {
                return;
            }

            unset($this->memo[$entry['rule']][$entry['offset']]);
            if ($this->memo[$entry['rule']] === []) {
                unset($this->memo[$entry['rule']]);
            }
        }
    }
}
