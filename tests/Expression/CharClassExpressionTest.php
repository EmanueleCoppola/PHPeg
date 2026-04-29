<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Expression;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Expression\CharClassExpression;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CharClassExpressionTest extends TestCase
{
    /**
     * Verifies character class accessors, descriptions, and matching.
     */
    public function testMatchesCharacterClasses(): void
    {
        $expression = new CharClassExpression('[a-z]');
        $grammar = GrammarBuilder::create()->grammar('Start')->rule('Start', $expression)->build();

        self::assertSame('[a-z]', $expression->pattern());
        self::assertSame('[a-z]', $expression->describe());
        self::assertTrue($grammar->parse('a')->isSuccess());
        self::assertFalse($grammar->parse('1')->isSuccess());
    }

    /**
     * Rejects invalid character class patterns.
     */
    public function testRejectsInvalidPatterns(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CharClassExpression('a-z');
    }
}