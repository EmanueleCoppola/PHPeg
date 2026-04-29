<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Expression;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Expression\ZeroOrMoreExpression;
use PHPUnit\Framework\TestCase;

class ZeroOrMoreExpressionTest extends TestCase
{
    /**
     * Verifies zero-or-more repetition.
     */
    public function testMatchesRepeatedInput(): void
    {
        $builder = GrammarBuilder::create();
        $expression = new ZeroOrMoreExpression($builder->literal('a'));
        $grammar = $builder->grammar('Start')->rule('Start', $expression)->build();

        self::assertSame('"a"*', $expression->describe());
        self::assertTrue($grammar->parse('aaa')->isSuccess());
        self::assertTrue($grammar->parse('')->isSuccess());
    }
}