<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Feature;

use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Grammar\Grammar;
use EmanueleCoppola\PHPeg\Loader\Peg\PegGrammarLoader;
use PHPUnit\Framework\TestCase;

class GrammarFeatureTest extends TestCase
{
    public function testBuilderApiParsesValidInput(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Start')
            ->rule('Start', $g->seq($g->literal('a'), $g->literal('b'), $g->literal('c')))
            ->build();

        self::assertTrue($grammar->parse('abc')->isSuccess());
    }

    public function testPegSyntaxParsesValidInput(): void
    {
        $grammar = (new PegGrammarLoader())->fromString(<<<'PEG'
Start <- 'a' 'b' 'c'
PEG);

        self::assertTrue($grammar->parse('abc')->isSuccess());
    }

    public function testJsonLikeObjectParsesWithBuilderApi(): void
    {
        $grammar = $this->jsonGrammarFromBuilder();
        $result = $grammar->parse('{"name":"Manu","age":26,"active":true}');

        self::assertTrue($result->isSuccess());
        self::assertSame('Json', $result->node()?->name());
        self::assertNotNull($result->node()?->firstChild('Value'));
    }

    public function testJsonLikeObjectParsesWithPegGrammar(): void
    {
        $grammar = (new PegGrammarLoader())->fromString($this->jsonPeg());
        $result = $grammar->parse('[1, 2, 3, null, false]');

        self::assertTrue($result->isSuccess());
        self::assertSame('Json', $result->node()?->name());
    }

    public function testFailureReturnsUsefulErrorInformation(): void
    {
        $grammar = (new PegGrammarLoader())->fromString($this->jsonPeg());
        $result = $grammar->parse('{"name":}');
        $error = $result->error();

        self::assertFalse($result->isSuccess());
        self::assertNotNull($error);
        self::assertGreaterThan(0, $error?->offset() ?? 0);
        self::assertGreaterThan(0, $error?->line() ?? 0);
        self::assertGreaterThan(0, $error?->column() ?? 0);
        self::assertNotSame('', $error?->snippet() ?? '');
    }

    private function jsonGrammarFromBuilder(): Grammar
    {
        $g = GrammarBuilder::create();

        return $g->grammar('Json')
            ->rule('Json', $g->seq($g->ref('Spacing'), $g->ref('Value'), $g->ref('Spacing')))
            ->rule('Value', $g->choice(
                $g->ref('Object'),
                $g->ref('Array'),
                $g->ref('String'),
                $g->ref('Number'),
                $g->literal('true'),
                $g->literal('false'),
                $g->literal('null'),
            ))
            ->rule('Object', $g->seq(
                $g->literal('{'),
                $g->ref('Spacing'),
                $g->optional($g->ref('PairList')),
                $g->ref('Spacing'),
                $g->literal('}'),
            ))
            ->rule('PairList', $g->seq(
                $g->ref('Pair'),
                $g->zeroOrMore($g->seq($g->ref('Spacing'), $g->literal(','), $g->ref('Spacing'), $g->ref('Pair'))),
            ))
            ->rule('Pair', $g->seq(
                $g->ref('String'),
                $g->ref('Spacing'),
                $g->literal(':'),
                $g->ref('Spacing'),
                $g->ref('Value'),
            ))
            ->rule('Array', $g->seq(
                $g->literal('['),
                $g->ref('Spacing'),
                $g->optional($g->ref('ValueList')),
                $g->ref('Spacing'),
                $g->literal(']'),
            ))
            ->rule('ValueList', $g->seq(
                $g->ref('Value'),
                $g->zeroOrMore($g->seq($g->ref('Spacing'), $g->literal(','), $g->ref('Spacing'), $g->ref('Value'))),
            ))
            ->rule('String', $g->seq($g->literal('"'), $g->zeroOrMore($g->ref('Char')), $g->literal('"')))
            ->rule('Char', $g->seq($g->not($g->literal('"')), $g->any()))
            ->rule('Number', $g->seq(
                $g->optional($g->literal('-')),
                $g->oneOrMore($g->charClass('[0-9]')),
                $g->optional($g->seq($g->literal('.'), $g->oneOrMore($g->charClass('[0-9]')))),
            ))
            ->rule('Spacing', $g->zeroOrMore($g->charClass('[ \t\r\n]')))
            ->build();
    }

    private function jsonPeg(): string
    {
        return <<<'PEG'
Json       <- Spacing Value Spacing
Value      <- Object / Array / String / Number / 'true' / 'false' / 'null'
Object     <- '{' Spacing PairList? Spacing '}'
PairList   <- Pair (Spacing ',' Spacing Pair)*
Pair       <- String Spacing ':' Spacing Value
Array      <- '[' Spacing ValueList? Spacing ']'
ValueList  <- Value (Spacing ',' Spacing Value)*
String     <- '"' Char* '"'
Char       <- !'"' .
Number     <- '-'? [0-9]+ ('.' [0-9]+)?
Spacing    <- [ \t\r\n]*
PEG;
    }
}
