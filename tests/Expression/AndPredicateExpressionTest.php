<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Expression;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPPeg\Expression\AndPredicateExpression;
use PHPUnit\Framework\TestCase;

class AndPredicateExpressionTest extends TestCase
{
    /**
     * Verifies positive lookahead behavior.
     */
    public function testMatchesWithoutConsumingInput(): void
    {
        $builder = GrammarBuilder::create();
        $expression = new AndPredicateExpression($builder->literal('a'));
        $grammar = $builder->grammar('Start')->rule('Start', $builder->seq($expression, $builder->literal('a')))->build();

        self::assertSame('&"a"', $expression->describe());
        self::assertSame('"a"', $expression->expression()->describe());
        self::assertTrue($grammar->parse('a')->isSuccess());
        self::assertFalse($grammar->parse('b')->isSuccess());
    }
}