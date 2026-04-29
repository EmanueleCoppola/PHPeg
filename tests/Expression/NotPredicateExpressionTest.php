<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Expression;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Expression\NotPredicateExpression;
use PHPUnit\Framework\TestCase;

class NotPredicateExpressionTest extends TestCase
{
    /**
     * Verifies negative lookahead behavior.
     */
    public function testMatchesWhenTheOperandDoesNotMatch(): void
    {
        $builder = GrammarBuilder::create();
        $expression = new NotPredicateExpression($builder->literal('b'));
        $grammar = $builder->grammar('Start')->rule('Start', $builder->seq($expression, $builder->literal('a')))->build();

        self::assertSame('!"b"', $expression->describe());
        self::assertSame('"b"', $expression->expression()->describe());
        self::assertTrue($grammar->parse('a')->isSuccess());
        self::assertFalse($grammar->parse('b')->isSuccess());
    }
}