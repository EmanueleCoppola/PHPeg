<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Unit;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use PHPUnit\Framework\TestCase;

class RegexExpressionTest extends TestCase
{
    public function testRegexMatchesAtCurrentOffset(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->seq($g->literal('a'), $g->regex('\d+')))
            ->build();

        self::assertTrue($grammar->parse('a123')->isSuccess());
    }

    public function testRegexDoesNotMatchLaterInput(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->regex('\d+'))
            ->build();

        self::assertFalse($grammar->parse('a123')->isSuccess());
    }

    public function testRegexConsumesTheCorrectText(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->regex('\d+\.\d+'))
            ->build();
        $result = $grammar->parse('12.50');

        self::assertTrue($result->isSuccess());
        self::assertSame('12.50', $result->matchedText());
    }

    public function testRegexFailureRestoresInputOffset(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->choice(
                $g->seq($g->regex('\d+'), $g->literal('x')),
                $g->seq($g->literal('1'), $g->literal('a')),
            ))
            ->build();

        self::assertTrue($grammar->parse('1a')->isSuccess());
    }

    public function testRegexSupportsUnicodeMode(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->regex('\p{L}+'))
            ->build();

        self::assertTrue($grammar->parse('éèà')->isSuccess());
    }
}
