<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Loader\CleanPeg;

use EmanueleCoppola\PHPeg\Expression\LakeExpression;
use EmanueleCoppola\PHPeg\Loader\CleanPeg\CleanPegGrammarParser;
use PHPUnit\Framework\TestCase;

class CleanPegGrammarParserTest extends TestCase
{
    /**
     * Verifies CleanPeg source can be parsed into a grammar.
     */
    public function testParsesCleanPegSource(): void
    {
        $grammar = CleanPegGrammarParser::parse("Start = \"{\" <BodyWater> \"}\"\n", 'Start', null);

        self::assertSame('Start', $grammar->startRule());
        self::assertTrue($grammar->parse('{abc}')->isSuccess());
        self::assertInstanceOf(LakeExpression::class, $grammar->rule('Start')?->expression()->expressions()[1] ?? null);
    }
}
