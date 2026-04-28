<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Expression;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPPeg\Expression\LiteralExpression;
use PHPUnit\Framework\TestCase;

class LiteralExpressionTest extends TestCase
{
    /**
     * Verifies literal accessors, descriptions, and matching.
     */
    public function testMatchesLiteralText(): void
    {
        $expression = new LiteralExpression('abc');
        $grammar = GrammarBuilder::create()->grammar('Start')->rule('Start', $expression)->build();

        self::assertSame('abc', $expression->literal());
        self::assertSame('"abc"', $expression->describe());
        self::assertTrue($grammar->parse('abc')->isSuccess());
        self::assertFalse($grammar->parse('abd')->isSuccess());
    }
}