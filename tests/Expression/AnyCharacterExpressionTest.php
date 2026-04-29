<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Expression;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Expression\AnyCharacterExpression;
use PHPUnit\Framework\TestCase;

class AnyCharacterExpressionTest extends TestCase
{
    /**
     * Verifies wildcard matching.
     */
    public function testMatchesAnySingleCharacter(): void
    {
        $expression = new AnyCharacterExpression();
        $grammar = GrammarBuilder::create()->grammar('Start')->rule('Start', $expression)->build();

        self::assertSame('.', $expression->describe());
        self::assertTrue($grammar->parse('a')->isSuccess());
        self::assertFalse($grammar->parse('')->isSuccess());
    }
}