<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Expression;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPPeg\Expression\EndOfInputExpression;
use PHPUnit\Framework\TestCase;

class EndOfInputExpressionTest extends TestCase
{
    /**
     * Verifies end-of-input matching.
     */
    public function testMatchesOnlyAtTheEndOfInput(): void
    {
        $expression = new EndOfInputExpression();
        $grammar = GrammarBuilder::create()->grammar('Start')->rule('Start', $expression)->build();

        self::assertSame('EOF', $expression->describe());
        self::assertTrue($grammar->parse('')->isSuccess());
        self::assertFalse($grammar->parse('a')->isSuccess());
    }
}