<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Expression;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPPeg\Expression\SequenceExpression;
use PHPUnit\Framework\TestCase;

class SequenceExpressionTest extends TestCase
{
    /**
     * Verifies sequence accessors and ordering.
     */
    public function testMatchesExpressionsInOrder(): void
    {
        $builder = GrammarBuilder::create();
        $expression = new SequenceExpression([$builder->literal('a'), $builder->literal('b')]);
        $grammar = $builder->grammar('Start')->rule('Start', $expression)->build();

        self::assertCount(2, $expression->expressions());
        self::assertSame('sequence', $expression->describe());
        self::assertTrue($grammar->parse('ab')->isSuccess());
        self::assertFalse($grammar->parse('ba')->isSuccess());
    }
}