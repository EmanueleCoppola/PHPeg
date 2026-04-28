<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Unit;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use PHPUnit\Framework\TestCase;

class EndOfInputExpressionTest extends TestCase
{
    public function testEofSucceedsAtEndOfInput(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->seq($g->literal('a'), $g->eof()))
            ->build();

        self::assertTrue($grammar->parse('a')->isSuccess());
    }

    public function testEofFailsWhenInputRemains(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->seq($g->literal('a'), $g->eof()))
            ->build();

        self::assertFalse($grammar->parse('ab')->isSuccess());
    }

    public function testEofDoesNotConsumeInput(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->eof())
            ->build();
        $result = $grammar->parse('');

        self::assertTrue($result->isSuccess());
        self::assertSame(0, $result->finalOffset());
    }
}
