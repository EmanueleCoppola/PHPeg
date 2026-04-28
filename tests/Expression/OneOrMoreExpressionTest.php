<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Expression;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPPeg\Expression\OneOrMoreExpression;
use PHPUnit\Framework\TestCase;

class OneOrMoreExpressionTest extends TestCase
{
    /**
     * Verifies one-or-more repetition.
     */
    public function testRequiresAtLeastOneMatch(): void
    {
        $builder = GrammarBuilder::create();
        $expression = new OneOrMoreExpression($builder->literal('a'));
        $grammar = $builder->grammar('Start')->rule('Start', $expression)->build();

        self::assertSame('"a"+', $expression->describe());
        self::assertTrue($grammar->parse('aaa')->isSuccess());
        self::assertFalse($grammar->parse('')->isSuccess());
    }
}