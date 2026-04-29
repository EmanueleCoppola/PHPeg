<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Lake;

use EmanueleCoppola\PHPeg\Expression\AndPredicateExpression;
use EmanueleCoppola\PHPeg\Expression\ChoiceExpression;
use EmanueleCoppola\PHPeg\Expression\EndOfInputExpression;
use EmanueleCoppola\PHPeg\Expression\ExpressionInterface;
use EmanueleCoppola\PHPeg\Expression\LakeExpression;
use EmanueleCoppola\PHPeg\Expression\NotPredicateExpression;
use EmanueleCoppola\PHPeg\Expression\OneOrMoreExpression;
use EmanueleCoppola\PHPeg\Expression\OptionalExpression;
use EmanueleCoppola\PHPeg\Expression\RuleReferenceExpression;
use EmanueleCoppola\PHPeg\Expression\SequenceExpression;
use EmanueleCoppola\PHPeg\Expression\ZeroOrMoreExpression;
use EmanueleCoppola\PHPeg\Grammar\Grammar;
use EmanueleCoppola\PHPeg\Grammar\Rule;

/**
 * Derives stop continuations for all lake expressions in a grammar.
 */
class LakeAnalyzer
{
    /**
     * @var array<int, list<LakeStopSequence>>
     */
    private array $lakeStops = [];

    /**
     * @var array<string, true>
     */
    private array $visited = [];

    /**
     * @var array<string, Rule>
     */
    private array $rules;

    private string $startRule;

    /**
     * Analyzes a grammar and returns the compiled lake plan.
     */
    public static function analyze(Grammar $grammar): LakePlan
    {
        $analyzer = new self($grammar);
        $analyzer->walk();

        return new LakePlan($analyzer->lakeStops);
    }

    /**
     * @param array<string, Rule> $rules
     */
    private function __construct(Grammar $grammar)
    {
        $this->rules = $grammar->rules();
        $this->startRule = $grammar->startRule();
    }

    /**
     * Traverses the grammar from the start rule.
     */
    private function walk(): void
    {
        $startRule = $this->rules[$this->startRule] ?? null;
        if ($startRule === null) {
            return;
        }

        $this->analyzeExpression($startRule->expression(), [new EndOfInputExpression()], []);

        foreach ($this->lakeStops as $lakeId => $sequences) {
            if ($sequences === []) {
                throw new LakeAnalysisException(sprintf('Unable to determine a safe stop set for lake expression #%d.', $lakeId));
            }
        }
    }

    /**
     * @param list<ExpressionInterface> $continuation
     * @param list<string> $ruleStack
     */
    private function analyzeExpression(ExpressionInterface $expression, array $continuation, array $ruleStack): void
    {
        $stateKey = spl_object_id($expression) . '|' . $this->continuationKey($continuation);
        if (isset($this->visited[$stateKey])) {
            return;
        }

        $this->visited[$stateKey] = true;

        if ($expression instanceof LakeExpression) {
            $this->recordLakeStop($expression, $continuation);

            return;
        }

        if ($expression instanceof SequenceExpression) {
            $items = $expression->expressions();
            for ($index = count($items) - 1; $index >= 0; $index--) {
                $suffix = array_slice($items, $index + 1);
                if ($continuation !== []) {
                    $suffix = [...$suffix, ...$continuation];
                }

                $this->analyzeExpression($items[$index], $suffix, $ruleStack);
            }

            return;
        }

        if ($expression instanceof ChoiceExpression) {
            foreach ($expression->alternatives() as $alternative) {
                $this->analyzeExpression($alternative, $continuation, $ruleStack);
            }

            return;
        }

        if ($expression instanceof ZeroOrMoreExpression || $expression instanceof OneOrMoreExpression) {
            $loopContinuation = [...[$expression], ...$continuation];
            $this->analyzeExpression($expression->expression(), $loopContinuation, $ruleStack);

            return;
        }

        if ($expression instanceof OptionalExpression) {
            $this->analyzeExpression($expression->expression(), $continuation, $ruleStack);

            return;
        }

        if ($expression instanceof AndPredicateExpression || $expression instanceof NotPredicateExpression) {
            $this->analyzeExpression($expression->expression(), $continuation, $ruleStack);

            return;
        }

        if ($expression instanceof RuleReferenceExpression) {
            $ruleName = $expression->ruleName();
            if (in_array($ruleName, $ruleStack, true)) {
                return;
            }

            $rule = $this->rules[$ruleName] ?? null;
            if ($rule === null) {
                return;
            }

            $this->analyzeExpression($rule->expression(), $continuation, [...$ruleStack, $ruleName]);
        }
    }

    /**
     * @param list<ExpressionInterface> $continuation
     */
    private function recordLakeStop(LakeExpression $lake, array $continuation): void
    {
        $lakeId = spl_object_id($lake);
        $signature = $this->continuationKey($continuation);

        foreach ($this->lakeStops[$lakeId] ?? [] as $sequence) {
            if ($sequence->signature() === $signature) {
                return;
            }
        }

        $this->lakeStops[$lakeId] ??= [];
        $this->lakeStops[$lakeId][] = new LakeStopSequence($continuation);
    }

    /**
     * Returns a stable continuation signature.
     *
     * @param list<ExpressionInterface> $continuation
     */
    private function continuationKey(array $continuation): string
    {
        return implode(',', array_map(static fn (ExpressionInterface $expression): string => (string) spl_object_id($expression), $continuation));
    }
}
