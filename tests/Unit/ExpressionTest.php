<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Unit;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use PHPUnit\Framework\TestCase;

class ExpressionTest extends TestCase
{
    public function testLiteralExpressionMatchesExactStrings(): void
    {
        $grammar = GrammarBuilder::create()
            ->grammar('Start')
            ->rule('Start', GrammarBuilder::create()->literal('hello'))
            ->build();

        $result = $grammar->parse('hello');

        self::assertTrue($result->isSuccess());
        self::assertSame('hello', $result->matchedText());
    }

    public function testLiteralExpressionFailsCorrectly(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')->rule('Start', $g->literal('hello'))->build();

        $result = $grammar->parse('world');

        self::assertFalse($result->isSuccess());
        self::assertNotNull($result->error());
        self::assertStringContainsString('Expected: "hello"', $result->error()?->message() ?? '');
    }

    public function testSequenceMatchesMultipleExpressions(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')->rule('Start', $g->seq($g->literal('a'), $g->literal('b')))->build();

        self::assertTrue($grammar->parse('ab')->isSuccess());
    }

    public function testChoiceUsesOrderedChoiceBehavior(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')->rule('Start', $g->choice($g->literal('a'), $g->literal('ab')))->build();

        $result = $grammar->parse('ab');

        self::assertFalse($result->isSuccess());
        self::assertSame(1, $result->finalOffset());
    }

    public function testZeroOrMoreMatchesRepeatedExpressions(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')->rule('Start', $g->zeroOrMore($g->literal('a')))->build();

        $result = $grammar->parse('aaa');

        self::assertTrue($result->isSuccess());
        self::assertSame(3, $result->finalOffset());
    }

    public function testOneOrMoreRequiresAtLeastOneMatch(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')->rule('Start', $g->oneOrMore($g->literal('a')))->build();

        self::assertFalse($grammar->parse('')->isSuccess());
    }

    public function testOptionalExpressionSucceedsWithAndWithoutMatch(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')->rule('Start', $g->seq($g->optional($g->literal('a')), $g->literal('b')))->build();

        self::assertTrue($grammar->parse('ab')->isSuccess());
        self::assertTrue($grammar->parse('b')->isSuccess());
    }

    public function testRuleReferencesResolveCorrectly(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->ref('Word'))
            ->rule('Word', $g->literal('hello'))
            ->build();

        self::assertTrue($grammar->parse('hello')->isSuccess());
    }

    public function testCharacterClassesMatchExpectedChars(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')->rule('Start', $g->oneOrMore($g->charClass('[0-9]')))->build();

        self::assertTrue($grammar->parse('123')->isSuccess());
        self::assertFalse($grammar->parse('abc')->isSuccess());
    }

    public function testAnyCharacterMatchesOneChar(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')->rule('Start', $g->any())->build();

        self::assertTrue($grammar->parse('x')->isSuccess());
        self::assertFalse($grammar->parse('')->isSuccess());
    }

    public function testNegativeLookaheadWorks(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->seq($g->not($g->literal('a')), $g->literal('b')))
            ->build();

        self::assertTrue($grammar->parse('b')->isSuccess());
        self::assertFalse($grammar->parse('a')->isSuccess());
    }

    public function testPositiveLookaheadWorks(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->seq($g->and($g->literal('a')), $g->literal('a')))
            ->build();

        self::assertTrue($grammar->parse('a')->isSuccess());
        self::assertFalse($grammar->parse('b')->isSuccess());
    }
}
