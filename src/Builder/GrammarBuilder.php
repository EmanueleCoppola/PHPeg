<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Builder;

use InvalidArgumentException;
use EmanueleCoppola\PHPeg\Expression\AndPredicateExpression;
use EmanueleCoppola\PHPeg\Expression\AnyCharacterExpression;
use EmanueleCoppola\PHPeg\Expression\CharClassExpression;
use EmanueleCoppola\PHPeg\Expression\ChoiceExpression;
use EmanueleCoppola\PHPeg\Expression\EndOfInputExpression;
use EmanueleCoppola\PHPeg\Expression\ExpressionInterface;
use EmanueleCoppola\PHPeg\Expression\LiteralExpression;
use EmanueleCoppola\PHPeg\Expression\NotPredicateExpression;
use EmanueleCoppola\PHPeg\Expression\OneOrMoreExpression;
use EmanueleCoppola\PHPeg\Expression\OptionalExpression;
use EmanueleCoppola\PHPeg\Expression\RegexExpression;
use EmanueleCoppola\PHPeg\Expression\RuleReferenceExpression;
use EmanueleCoppola\PHPeg\Expression\SequenceExpression;
use EmanueleCoppola\PHPeg\Expression\ZeroOrMoreExpression;
use EmanueleCoppola\PHPeg\Grammar\Grammar;
use EmanueleCoppola\PHPeg\Grammar\Rule;

/**
 * Fluent grammar builder for declarative PHP grammar definitions.
 */
class GrammarBuilder
{
    /**
     * @var array<string, Rule>
     */
    private array $rules = [];

    private ?string $startRule = null;

    /**
     * Creates a new builder instance.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Sets the grammar start rule and returns the builder.
     */
    public function grammar(string $startRule): self
    {
        $this->startRule = $startRule;

        return $this;
    }

    /**
     * Adds or replaces a rule definition.
     */
    public function rule(string $name, ExpressionInterface $expression): self
    {
        if ($this->startRule === null) {
            $this->startRule = $name;
        }

        $this->rules[$name] = new Rule($name, $expression);

        return $this;
    }

    /**
     * Builds the immutable grammar instance.
     */
    public function build(): Grammar
    {
        if ($this->startRule === null) {
            throw new InvalidArgumentException('Cannot build a grammar without a start rule.');
        }

        return new Grammar($this->rules, $this->startRule);
    }

    /**
     * Creates a literal expression.
     */
    public function literal(string $literal): ExpressionInterface
    {
        return new LiteralExpression($literal);
    }

    /**
     * Creates a character class expression such as [0-9].
     */
    public function charClass(string $pattern): ExpressionInterface
    {
        return new CharClassExpression($pattern);
    }

    /**
     * Creates an anchored regex expression.
     */
    public function regex(string $pattern): ExpressionInterface
    {
        return new RegexExpression($pattern);
    }

    /**
     * Creates a sequence expression.
     */
    public function seq(ExpressionInterface ...$expressions): ExpressionInterface
    {
        return new SequenceExpression($expressions);
    }

    /**
     * Creates an ordered choice expression.
     */
    public function choice(ExpressionInterface ...$expressions): ExpressionInterface
    {
        return new ChoiceExpression($expressions);
    }

    /**
     * Creates a zero-or-more expression.
     */
    public function zeroOrMore(ExpressionInterface $expression): ExpressionInterface
    {
        return new ZeroOrMoreExpression($expression);
    }

    /**
     * Creates a one-or-more expression.
     */
    public function oneOrMore(ExpressionInterface $expression): ExpressionInterface
    {
        return new OneOrMoreExpression($expression);
    }

    /**
     * Creates an optional expression.
     */
    public function optional(ExpressionInterface $expression): ExpressionInterface
    {
        return new OptionalExpression($expression);
    }

    /**
     * Creates a rule reference expression.
     */
    public function ref(string $name): ExpressionInterface
    {
        return new RuleReferenceExpression($name);
    }

    /**
     * Creates an any-character expression.
     */
    public function any(): ExpressionInterface
    {
        return new AnyCharacterExpression();
    }

    /**
     * Creates an end-of-input expression.
     */
    public function eof(): ExpressionInterface
    {
        return new EndOfInputExpression();
    }

    /**
     * Creates a positive lookahead expression.
     */
    public function and(ExpressionInterface $expression): ExpressionInterface
    {
        return new AndPredicateExpression($expression);
    }

    /**
     * Creates a negative lookahead expression.
     */
    public function not(ExpressionInterface $expression): ExpressionInterface
    {
        return new NotPredicateExpression($expression);
    }

    /**
     * Short alias for one-or-more.
     */
    public function one(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->oneOrMore($expression);
    }

    /**
     * Short alias for zero-or-more.
     */
    public function many(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->zeroOrMore($expression);
    }

    /**
     * Short alias for optional.
     */
    public function maybe(ExpressionInterface $expression): ExpressionInterface
    {
        return $this->optional($expression);
    }

    /**
     * Short alias for choice.
     */
    public function or(ExpressionInterface ...$expressions): ExpressionInterface
    {
        return $this->choice(...$expressions);
    }
}
