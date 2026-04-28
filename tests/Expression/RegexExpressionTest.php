<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Expression;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPPeg\Expression\RegexExpression;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RegexExpressionTest extends TestCase
{
    /**
     * Verifies regex accessors, descriptions, and matching.
     */
    public function testMatchesRegexText(): void
    {
        $expression = new RegexExpression('[a-z]+');
        $grammar = GrammarBuilder::create()->grammar('Start')->rule('Start', $expression)->build();

        self::assertSame('[a-z]+', $expression->pattern());
        self::assertSame('regex([a-z]+)', $expression->describe());
        self::assertTrue($grammar->parse('abc')->isSuccess());
        self::assertFalse($grammar->parse('123')->isSuccess());
    }

    /**
     * Rejects invalid regex patterns.
     */
    public function testRejectsInvalidRegexPatterns(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RegexExpression('[');
    }
}