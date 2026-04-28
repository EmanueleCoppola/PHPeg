<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Loader\Peg;

use EmanueleCoppola\PHPPeg\Loader\Peg\PegGrammarParser;
use PHPUnit\Framework\TestCase;

class PegGrammarParserTest extends TestCase
{
    /**
     * Verifies PEG source can be parsed into a grammar.
     */
    public function testParsesPegSource(): void
    {
        $grammar = PegGrammarParser::parse('Start <- "a"');

        self::assertSame('Start', $grammar->startRule());
        self::assertTrue($grammar->parse('a')->isSuccess());
    }
}