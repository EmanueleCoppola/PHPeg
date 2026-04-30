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

    /**
     * Verifies CleanPeg water annotations are parsed and used during lake matching.
     */
    public function testParsesCleanPegWaterAnnotation(): void
    {
        $grammar = CleanPegGrammarParser::parse(<<<'CLEANPEG'
Start = "{" <Body> "}"
@water
Quoted = "\"" r'[^"]*' "\""
CLEANPEG, 'Start', null);

        $document = $grammar->parseDocument('{foo "bar" baz}');
        $body = $document->query('Body[kind="lake"]:first')->first();
        $quoted = $body?->childrenByName('Quoted')[0] ?? null;

        self::assertNotNull($body);
        self::assertSame('Body', $body?->name());
        self::assertCount(1, $body?->childrenByName('Quoted') ?? []);
        self::assertSame('water', $quoted?->attribute('kind'));
        self::assertSame('"bar"', $quoted?->text());
    }

    /**
     * Verifies CleanPeg lake declarations can use a local water profile.
     */
    public function testParsesCleanPegLakeProfileAnnotation(): void
    {
        $grammar = CleanPegGrammarParser::parse(<<<'CLEANPEG'
<BodyWater> = r'[^{}]+'
Program = "{" <BodyWater> "}"
@water
Whitespace = r'[ \t\r\n]+'
CLEANPEG, 'Program', null);

        $document = $grammar->parseDocument('{foo bar}');
        $lake = $document->query('BodyWater[kind="lake"]:first')->first();
        $water = $document->query('BodyWater[kind="water"]:first')->first();

        self::assertNotNull($lake);
        self::assertNotNull($water);
        self::assertSame('foo bar', $lake?->text());
        self::assertSame('foo bar', $water?->text());
    }
}
