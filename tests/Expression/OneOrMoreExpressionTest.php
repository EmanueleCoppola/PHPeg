<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Expression;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Expression\OneOrMoreExpression;
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