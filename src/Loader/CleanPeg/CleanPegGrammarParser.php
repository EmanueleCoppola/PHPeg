<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Loader\CleanPeg;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPPeg\Error\GrammarSyntaxError;
use EmanueleCoppola\PHPPeg\Expression\ExpressionInterface;
use EmanueleCoppola\PHPPeg\Grammar\Grammar;

/**
 * Parses CleanPeg grammar syntax into PHPPeg grammar objects.
 */
class CleanPegGrammarParser
{
    /**
     * @param list<CleanPegToken> $tokens
     */
    private function __construct(
        private readonly array $tokens,
        private readonly GrammarBuilder $builder,
        private readonly ?string $startRule,
        private readonly ?ExpressionInterface $skipExpression,
        private int $index = 0,
    ) {
    }

    /**
     * Parses CleanPeg source text into a grammar.
     */
    public static function parse(string $source, ?string $startRule = null, ?string $skipPattern = '[ \t\r\n]*'): Grammar
    {
        $builder = GrammarBuilder::create();
        $tokens = (new CleanPegTokenizer($source))->tokenize();
        $skipExpression = $skipPattern === null ? null : $builder->regex($skipPattern);
        $parser = new self($tokens, $builder, $startRule, $skipExpression);

        return $parser->parseGrammar();
    }

    private function parseGrammar(): Grammar
    {
        $firstRule = null;

        while (!$this->check('EOF')) {
            $this->consumeNewlines();
            if ($this->check('EOF')) {
                break;
            }

            $name = $this->consume('IDENT', 'expected rule name')->lexeme;
            $this->consume('EQUAL', 'expected "=" after rule name');
            $expression = $this->parseExpression();
            $this->builder->rule($name, $expression);
            $firstRule ??= $name;

            if (!$this->check('EOF')) {
                $this->consume('NEWLINE', 'expected end of rule');
            }
        }

        if ($firstRule === null) {
            throw new GrammarSyntaxError('CleanPeg', 1, 1, 'grammar does not contain any rule');
        }

        $this->builder->grammar($this->startRule ?? $firstRule);

        return $this->builder->build();
    }

    private function parseExpression(): ExpressionInterface
    {
        return $this->parseChoice();
    }

    private function parseChoice(): ExpressionInterface
    {
        $sequence = $this->parseSequence();
        $alternatives = [$sequence];

        while ($this->match('SLASH')) {
            $alternatives[] = $this->parseSequence();
        }

        return count($alternatives) === 1 ? $sequence : $this->builder->choice(...$alternatives);
    }

    private function parseSequence(): ExpressionInterface
    {
        $items = [];

        while ($this->isPrimaryStart()) {
            $items[] = $this->parsePostfix();
        }

        if ($items === []) {
            $token = $this->peek();
            throw new GrammarSyntaxError('CleanPeg', $token->line, $token->column, 'expected expression');
        }

        return count($items) === 1 ? $items[0] : $this->builder->seq(...$items);
    }

    private function parsePostfix(): ExpressionInterface
    {
        $expression = $this->parsePrimary();

        if ($this->match('QUESTION')) {
            return $this->builder->optional($expression);
        }

        if ($this->match('STAR')) {
            return $this->builder->zeroOrMore($expression);
        }

        if ($this->match('PLUS')) {
            return $this->builder->oneOrMore($expression);
        }

        return $expression;
    }

    private function parsePrimary(): ExpressionInterface
    {
        if ($this->match('STRING')) {
            return $this->wrapSkippable($this->builder->literal($this->previous()->lexeme));
        }

        if ($this->match('REGEX')) {
            return $this->wrapSkippable($this->builder->regex($this->previous()->lexeme));
        }

        if ($this->match('IDENT')) {
            $name = $this->previous()->lexeme;

            if ($name === 'EOF') {
                return $this->wrapSkippable($this->builder->eof());
            }

            return $this->wrapSkippable($this->builder->ref($name));
        }

        if ($this->match('LPAREN')) {
            $expression = $this->parseExpression();
            $this->consume('RPAREN', 'expected ")" after grouped expression');

            return $expression;
        }

        $token = $this->peek();
        throw new GrammarSyntaxError('CleanPeg', $token->line, $token->column, 'expected expression');
    }

    private function wrapSkippable(ExpressionInterface $expression): ExpressionInterface
    {
        if ($this->skipExpression === null) {
            return $expression;
        }

        return $this->builder->seq($this->skipExpression, $expression);
    }

    private function isPrimaryStart(): bool
    {
        if ($this->check('STRING') || $this->check('REGEX') || $this->check('LPAREN')) {
            return true;
        }

        if ($this->check('IDENT')) {
            return true;
        }

        return false;
    }

    private function consumeNewlines(): void
    {
        while ($this->match('NEWLINE')) {
        }
    }

    private function match(string $type): bool
    {
        if (!$this->check($type)) {
            return false;
        }

        $this->index++;

        return true;
    }

    private function consume(string $type, string $message): CleanPegToken
    {
        if ($this->check($type)) {
            return $this->tokens[$this->index++];
        }

        $token = $this->peek();
        throw new GrammarSyntaxError('CleanPeg', $token->line, $token->column, $message);
    }

    private function check(string $type): bool
    {
        return $this->peek()->type === $type;
    }

    private function peek(): CleanPegToken
    {
        return $this->tokens[$this->index];
    }

    private function previous(): CleanPegToken
    {
        return $this->tokens[$this->index - 1];
    }
}
