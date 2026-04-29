<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Loader\Peg;

use EmanueleCoppola\PHPeg\Expression\LakeExpression;
use EmanueleCoppola\PHPeg\Loader\Peg\PegGrammarParser;
use PHPUnit\Framework\TestCase;

class PegGrammarParserTest extends TestCase
{
    /**
     * Verifies PEG source can be parsed into a grammar.
     */
    public function testParsesPegSource(): void
    {
        $grammar = PegGrammarParser::parse('Start <- "{" <BodyWater> "}"');

        self::assertSame('Start', $grammar->startRule());
        self::assertTrue($grammar->parse('{abc}')->isSuccess());
        self::assertInstanceOf(LakeExpression::class, $grammar->rule('Start')?->expression()->expressions()[1] ?? null);
    }
}
