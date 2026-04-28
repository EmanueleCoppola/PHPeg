<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Loader\CleanPeg;

use EmanueleCoppola\PHPPeg\Loader\CleanPeg\CleanPegGrammarParser;
use PHPUnit\Framework\TestCase;

class CleanPegGrammarParserTest extends TestCase
{
    /**
     * Verifies CleanPeg source can be parsed into a grammar.
     */
    public function testParsesCleanPegSource(): void
    {
        $grammar = CleanPegGrammarParser::parse("Start = \"a\"\n", 'Start', null);

        self::assertSame('Start', $grammar->startRule());
        self::assertTrue($grammar->parse('a')->isSuccess());
    }
}