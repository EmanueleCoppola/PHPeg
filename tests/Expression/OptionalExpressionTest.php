<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Expression;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Expression\OptionalExpression;
use PHPUnit\Framework\TestCase;

class OptionalExpressionTest extends TestCase
{
    /**
     * Verifies optional accessors and zero-width matches.
     */
    public function testMatchesWhenPresentOrAbsent(): void
    {
        $builder = GrammarBuilder::create();
        $expression = new OptionalExpression($builder->literal('a'));
        $grammar = $builder->grammar('Start')->rule('Start', $expression)->build();

        self::assertSame('"a"?', $expression->describe());
        self::assertSame('"a"', $expression->expression()->describe());
        self::assertTrue($grammar->parse('a')->isSuccess());
        self::assertTrue($grammar->parse('')->isSuccess());
    }
}