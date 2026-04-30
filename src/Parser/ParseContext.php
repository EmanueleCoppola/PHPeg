<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Parser;

use EmanueleCoppola\PHPeg\Error\ParseError;
use EmanueleCoppola\PHPeg\Error\LeftRecursionException;
use EmanueleCoppola\PHPeg\Expression\ExpressionInterface;
use EmanueleCoppola\PHPeg\Expression\LakeExpression;
use EmanueleCoppola\PHPeg\Grammar\Grammar;
use EmanueleCoppola\PHPeg\Lake\LakeMatcher;
use EmanueleCoppola\PHPeg\Lake\LakePlan;
use EmanueleCoppola\PHPeg\Lake\LakePlanCache;
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

    /**
     * @var array<string, MatchResult|null>
     */
    private array $expressionMemo = [];

    /**
     * @var list<string>
     */
    private array $expressionMemoOrder = [];

    private int $failureSuppressionDepth = 0;

    /**
     * @var list<array<int, int>>
     */
    private array $bannedLakeIdStack = [];

    private readonly bool $memoizationEnabled;

    private readonly bool $optimizeErrors;

    private readonly bool $reuseEmptyMatches;

    private readonly ?int $maxCacheEntries;

    private readonly LakePlan $lakePlan;

    /**
     * Initializes a new ParseContext instance.
     */
    public function __construct(
        private readonly Grammar $grammar,
        private readonly InputBuffer $input,
        private readonly ParserOptions $options = new ParserOptions(),
    ) {
        $this->memoizationEnabled = $options->memoizationEnabled();
        $this->optimizeErrors = $options->optimizeErrors();
        $this->reuseEmptyMatches = $options->reuseEmptyMatches();
        $this->maxCacheEntries = $options->maxCacheEntries();
        $this->lakePlan = LakePlanCache::forGrammar($grammar);
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
     * Returns the compiled lake plan for this grammar.
     */
    public function lakePlan(): LakePlan
    {
        return $this->lakePlan;
    }

    /**
     * Matches an arbitrary expression with memoization.
     */
    public function matchExpression(ExpressionInterface $expression, int $offset): ?MatchResult
    {
        return $this->matchExpressionInternal($expression, $offset);
    }

    /**
     * Matches an arbitrary expression without recording failures.
     */
    public function matchExpressionSilently(ExpressionInterface $expression, int $offset): ?MatchResult
    {
        $this->failureSuppressionDepth++;
        try {
            return $this->matchExpressionInternal($expression, $offset);
        } finally {
            $this->failureSuppressionDepth--;
        }
    }

    /**
     * Matches a lake expression using the compiled lake plan.
     */
    public function matchLakeExpression(LakeExpression $lake, int $offset): ?MatchResult
    {
        return LakeMatcher::match($this, $lake, $offset);
    }

    /**
     * Runs a callback with one or more lake ids temporarily banned.
     *
     * @param array<int, int> $bannedLakeIds
     */
    public function withBannedLakeIds(array $bannedLakeIds, callable $callback): mixed
    {
        $this->bannedLakeIdStack[] = $bannedLakeIds;
        try {
            return $callback();
        } finally {
            array_pop($this->bannedLakeIdStack);
        }
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
        if ($this->failureSuppressionDepth > 0) {
            return;
        }

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

    /**
     * Applies the configured expression memoization limit.
     */
    private function trimExpressionMemo(): void
    {
        if ($this->maxCacheEntries === null) {
            return;
        }

        while (count($this->expressionMemoOrder) > $this->maxCacheEntries) {
            $key = array_shift($this->expressionMemoOrder);
            if ($key === null) {
                return;
            }

            unset($this->expressionMemo[$key]);
        }
    }

    /**
     * Matches an expression with memoization and lake-ban awareness.
     */
    private function matchExpressionInternal(ExpressionInterface $expression, int $offset): ?MatchResult
    {
        $cacheKey = null;
        if ($this->memoizationEnabled) {
            $cacheKey = $this->expressionMemoKey($expression, $offset);
            if (array_key_exists($cacheKey, $this->expressionMemo)) {
                return $this->expressionMemo[$cacheKey];
            }
        }

        if ($expression instanceof LakeExpression && $this->isLakeBanned($expression, $offset)) {
            if ($this->memoizationEnabled && $cacheKey !== null) {
                $this->expressionMemo[$cacheKey] = null;
                $this->expressionMemoOrder[] = $cacheKey;
                $this->trimExpressionMemo();
            }

            return null;
        }

        if (!$this->memoizationEnabled) {
            return $this->matchExpressionDirect($expression, $offset);
        }

        $result = $this->matchExpressionDirect($expression, $offset);
        $this->expressionMemo[$cacheKey] = $result;
        $this->expressionMemoOrder[] = $cacheKey;
        $this->trimExpressionMemo();

        return $result;
    }

    /**
     * Matches an expression without consulting the memoized cache.
     */
    private function matchExpressionDirect(ExpressionInterface $expression, int $offset): ?MatchResult
    {
        return $expression->match($this, $offset);
    }

    /**
     * Returns whether the current stop-match context bans the provided lake.
     */
    private function isLakeBanned(LakeExpression $lake, int $offset): bool
    {
        $lakeId = spl_object_id($lake);

        for ($index = count($this->bannedLakeIdStack) - 1; $index >= 0; $index--) {
            if (($this->bannedLakeIdStack[$index][$lakeId] ?? null) === $offset) {
                return true;
            }
        }

        return false;
    }

    /**
     * Builds a memoization key that includes the active lake-ban signature.
     */
    private function expressionMemoKey(ExpressionInterface $expression, int $offset): string
    {
        return $this->bannedLakeSignature() . '|' . spl_object_id($expression) . '|' . $offset;
    }

    /**
     * Returns a stable signature for the currently banned lake ids.
     */
    private function bannedLakeSignature(): string
    {
        if ($this->bannedLakeIdStack === []) {
            return '0';
        }

        $ids = [];
        foreach ($this->bannedLakeIdStack as $frame) {
            foreach ($frame as $lakeId => $originOffset) {
                $ids[$lakeId . '@' . $originOffset] = true;
            }
        }

        $keys = array_map('strval', array_keys($ids));
        sort($keys);

        return implode(',', $keys);
    }
}
