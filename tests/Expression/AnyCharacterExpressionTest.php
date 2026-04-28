<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Expression;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPPeg\Expression\AnyCharacterExpression;
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