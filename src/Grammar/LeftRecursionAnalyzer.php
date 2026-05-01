<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Grammar;

use EmanueleCoppola\PHPeg\Expression\AndPredicateExpression;
use EmanueleCoppola\PHPeg\Expression\AnyCharacterExpression;
use EmanueleCoppola\PHPeg\Expression\ChoiceExpression;
use EmanueleCoppola\PHPeg\Expression\EndOfInputExpression;
use EmanueleCoppola\PHPeg\Expression\ExpressionInterface;
use EmanueleCoppola\PHPeg\Expression\LakeExpression;
use EmanueleCoppola\PHPeg\Expression\LiteralExpression;
use EmanueleCoppola\PHPeg\Expression\NotPredicateExpression;
use EmanueleCoppola\PHPeg\Expression\OneOrMoreExpression;
use EmanueleCoppola\PHPeg\Expression\OptionalExpression;
use EmanueleCoppola\PHPeg\Expression\CharClassExpression;
use EmanueleCoppola\PHPeg\Expression\RegexExpression;
use EmanueleCoppola\PHPeg\Expression\RuleReferenceExpression;
use EmanueleCoppola\PHPeg\Expression\SequenceExpression;
use EmanueleCoppola\PHPeg\Expression\ZeroOrMoreExpression;

/**
 * Detects whether a grammar contains left recursion.
 */
class LeftRecursionAnalyzer
{
    /**
     * @var array<string, Rule>
     */
    private array $rules;

    /**
     * @var array<string, bool>
     */
    private array $nullableRules = [];

    /**
     * @var array<int, bool>
     */
    private array $nullableExpressionMemo = [];

    /**
     * @var array<int, bool>
     */
    private array $nullableExpressionStack = [];

    /**
     * @var array<int, array<string, true>>
     */
    private array $leftCornerMemo = [];

    /**
     * @var array<int, bool>
     */
    private array $leftCornerStack = [];

    /**
     * Detects whether the provided grammar contains left recursion.
     */
    public static function detect(Grammar $grammar): bool
    {
        return (new self($grammar))->hasLeftRecursion();
    }

    /**
     * Initializes a new LeftRecursionAnalyzer instance.
     */
    private function __construct(Grammar $grammar)
    {
        $this->rules = $grammar->rules();
    }

    /**
     * Returns whether any rule in the grammar is left-recursive.
     */
    private function hasLeftRecursion(): bool
    {
        $this->computeNullableRules();
        $this->leftCornerMemo = [];

        /**
         * @var array<string, array<string, true>> $graph
         */
        $graph = [];
        foreach ($this->rules as $ruleName => $rule) {
            $graph[$ruleName] = $this->directLeftCornerRules($rule->expression());
        }

        foreach ($this->rules as $ruleName => $rule) {
            if ($this->ruleIsLeftRecursive($ruleName, $graph)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Computes the nullable rules with a fixpoint iteration.
     */
    private function computeNullableRules(): void
    {
        do {
            $changed = false;
            $this->nullableExpressionMemo = [];
            $this->nullableExpressionStack = [];

            foreach ($this->rules as $ruleName => $rule) {
                if (isset($this->nullableRules[$ruleName])) {
                    continue;
                }

                if ($this->isNullable($rule->expression())) {
                    $this->nullableRules[$ruleName] = true;
                    $changed = true;
                }
            }
        } while ($changed);
    }

    /**
     * Returns whether the provided rule can derive itself without consuming input.
     */
    private function ruleIsLeftRecursive(string $ruleName, array $graph): bool
    {
        return $this->reachable($ruleName, $ruleName, $graph, []);
    }

    /**
     * Returns the set of rule names that can appear at the left edge of an expression.
     *
     * @return array<string, true>
     */
    private function directLeftCornerRules(ExpressionInterface $expression): array
    {
        $expressionId = spl_object_id($expression);
        if (isset($this->leftCornerMemo[$expressionId])) {
            return $this->leftCornerMemo[$expressionId];
        }

        if (isset($this->leftCornerStack[$expressionId])) {
            return [];
        }

        $this->leftCornerStack[$expressionId] = true;
        $result = [];

        if ($expression instanceof RuleReferenceExpression) {
            $result[$expression->ruleName()] = true;
        } elseif ($expression instanceof ChoiceExpression) {
            foreach ($expression->alternatives() as $alternative) {
                $result += $this->directLeftCornerRules($alternative);
            }
        } elseif ($expression instanceof SequenceExpression) {
            foreach ($expression->expressions() as $part) {
                $result += $this->directLeftCornerRules($part);

                if (!$this->isNullable($part)) {
                    break;
                }
            }
        } elseif ($expression instanceof OptionalExpression || $expression instanceof ZeroOrMoreExpression || $expression instanceof OneOrMoreExpression) {
            $result += $this->directLeftCornerRules($expression->expression());
        } elseif ($expression instanceof AndPredicateExpression || $expression instanceof NotPredicateExpression) {
            $result += $this->directLeftCornerRules($expression->expression());
        }

        unset($this->leftCornerStack[$expressionId]);
        $this->leftCornerMemo[$expressionId] = $result;

        return $result;
    }

    /**
     * Returns whether the provided expression can match without consuming input.
     */
    private function isNullable(ExpressionInterface $expression): bool
    {
        $expressionId = spl_object_id($expression);
        if (array_key_exists($expressionId, $this->nullableExpressionMemo)) {
            return $this->nullableExpressionMemo[$expressionId];
        }

        if (isset($this->nullableExpressionStack[$expressionId])) {
            return false;
        }

        $this->nullableExpressionStack[$expressionId] = true;

        $result = match (true) {
            $expression instanceof EndOfInputExpression => true,
            $expression instanceof LakeExpression => true,
            $expression instanceof OptionalExpression => true,
            $expression instanceof ZeroOrMoreExpression => true,
            $expression instanceof AndPredicateExpression => true,
            $expression instanceof NotPredicateExpression => true,
            $expression instanceof OneOrMoreExpression => $this->isNullable($expression->expression()),
            $expression instanceof ChoiceExpression => $this->isAnyNullable($expression->alternatives()),
            $expression instanceof SequenceExpression => $this->isAllNullable($expression->expressions()),
            $expression instanceof RuleReferenceExpression => isset($this->nullableRules[$expression->ruleName()]),
            $expression instanceof LiteralExpression => false,
            $expression instanceof CharClassExpression => false,
            $expression instanceof RegexExpression => $expression->canMatchEmpty(),
            $expression instanceof AnyCharacterExpression => false,
            default => false,
        };

        unset($this->nullableExpressionStack[$expressionId]);
        $this->nullableExpressionMemo[$expressionId] = $result;

        return $result;
    }

    /**
     * Returns whether any expression in the list is nullable.
     *
     * @param list<ExpressionInterface> $expressions
     */
    private function isAnyNullable(array $expressions): bool
    {
        foreach ($expressions as $expression) {
            if ($this->isNullable($expression)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether every expression in the list is nullable.
     *
     * @param list<ExpressionInterface> $expressions
     */
    private function isAllNullable(array $expressions): bool
    {
        foreach ($expressions as $expression) {
            if (!$this->isNullable($expression)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns whether a rule is reachable from the current rule through left-corner links.
     *
     * @param array<string, array<string, true>> $graph
     * @param array<string, true> $seen
     */
    private function reachable(string $origin, string $current, array $graph, array $seen): bool
    {
        if (isset($seen[$current])) {
            return false;
        }

        $seen[$current] = true;

        foreach (array_keys($graph[$current] ?? []) as $next) {
            if ($next === $origin) {
                return true;
            }

            if ($this->reachable($origin, $next, $graph, $seen)) {
                return true;
            }
        }

        return false;
    }
}
