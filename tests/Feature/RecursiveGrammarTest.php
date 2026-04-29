<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Feature;

use EmanueleCoppola\PHPeg\Ast\AstNode;
use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Grammar\Grammar;
use EmanueleCoppola\PHPeg\Loader\Peg\PegGrammarLoader;
use PHPUnit\Framework\TestCase;

class RecursiveGrammarTest extends TestCase
{
    public function testBuilderApiCanParseOneBlockWithNoNestedBlocks(): void
    {
        $result = $this->recursiveBuilderGrammar()->parse('block main { print "hello" }');

        self::assertTrue($result->isSuccess());
        self::assertSame('Program', $result->node()?->name());
    }

    public function testBuilderApiCanParseDeeplyNestedBlocks(): void
    {
        $input = $this->recursiveProgramInput();
        $result = $this->recursiveBuilderGrammar()->parse($input);

        self::assertTrue($result->isSuccess());
        self::assertSame(strlen($input), $result->finalOffset());
    }

    public function testPegLoaderCanParseDeeplyNestedBlocks(): void
    {
        $grammar = (new PegGrammarLoader())->fromString($this->recursivePeg());
        $result = $grammar->parse($this->recursiveProgramInput(), 'Program');

        self::assertTrue($result->isSuccess());
        self::assertSame('Program', $result->node()?->name());
    }

    public function testAstPreservesNestedBlockHierarchy(): void
    {
        $result = $this->recursiveBuilderGrammar()->parse($this->recursiveProgramInput());
        $program = $result->node();
        $block = $program?->firstChild('Block');
        $nestedStatement = $this->childByNameAt($block, 'Statement', 1);
        $nestedBlock = $nestedStatement?->firstChild('Block');
        $deeperStatement = $this->childByNameAt($nestedBlock, 'Statement', 1);
        $deeperBlock = $deeperStatement?->firstChild('Block');

        self::assertTrue($result->isSuccess());
        self::assertSame('main', trim((string) $block?->firstChild('Identifier')?->text()));
        self::assertSame('nested', trim((string) $nestedBlock?->firstChild('Identifier')?->text()));
        self::assertSame('deeper', trim((string) $deeperBlock?->firstChild('Identifier')?->text()));
    }

    public function testParserDetectsDirectLeftRecursionAndReturnsClearError(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Expr')
            ->rule('Expr', $g->choice(
                $g->seq($g->ref('Expr'), $g->literal('+'), $g->ref('Number')),
                $g->ref('Number'),
            ))
            ->rule('Number', $g->oneOrMore($g->charClass('[0-9]')))
            ->build();

        $result = $grammar->parse('1+2');

        self::assertFalse($result->isSuccess());
        self::assertStringContainsString('Left-recursive rule detected: Expr', $result->error()?->message() ?? '');
    }

    public function testRecursiveParseFailureRestoresInputPositionCorrectly(): void
    {
        $result = $this->recursiveBuilderGrammar()->parse(<<<'TEXT'
block main {
    print "hello"
    block nested {
        print "inside"
TEXT);

        self::assertFalse($result->isSuccess());
        self::assertGreaterThan(strlen('block main {'), $result->error()?->offset() ?? 0);
    }

    public function testMutualRecursionWorks(): void
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('A')
            ->rule('A', $g->choice(
                $g->seq($g->literal('a'), $g->ref('B')),
                $g->literal('x'),
            ))
            ->rule('B', $g->choice(
                $g->seq($g->literal('b'), $g->ref('A')),
                $g->literal('y'),
            ))
            ->build();

        self::assertTrue($grammar->parse('x')->isSuccess());
        self::assertTrue($grammar->parse('ay')->isSuccess());
        self::assertTrue($grammar->parse('abx')->isSuccess());
        self::assertTrue($grammar->parse('ababx')->isSuccess());
        self::assertFalse($grammar->parse('aba')->isSuccess());
    }

    private function recursiveBuilderGrammar(): Grammar
    {
        $g = GrammarBuilder::create();

        return $g->grammar('Program')
            ->rule('Program', $g->seq(
                $g->ref('Spacing'),
                $g->ref('Block'),
                $g->ref('Spacing'),
            ))
            ->rule('Block', $g->seq(
                $g->literal('block'),
                $g->ref('Spacing'),
                $g->ref('Identifier'),
                $g->ref('Spacing'),
                $g->literal('{'),
                $g->ref('Spacing'),
                $g->zeroOrMore($g->ref('Statement')),
                $g->ref('Spacing'),
                $g->literal('}'),
            ))
            ->rule('Statement', $g->seq(
                $g->ref('Spacing'),
                $g->choice(
                    $g->ref('PrintStatement'),
                    $g->ref('Block'),
                ),
            ))
            ->rule('PrintStatement', $g->seq(
                $g->literal('print'),
                $g->ref('Spacing'),
                $g->ref('String'),
                $g->ref('Spacing'),
            ))
            ->rule('Identifier', $g->seq(
                $g->charClass('[a-zA-Z_]'),
                $g->zeroOrMore($g->charClass('[a-zA-Z0-9_]')),
            ))
            ->rule('String', $g->seq(
                $g->literal('"'),
                $g->zeroOrMore($g->ref('StringChar')),
                $g->literal('"'),
            ))
            ->rule('StringChar', $g->seq(
                $g->not($g->literal('"')),
                $g->any(),
            ))
            ->rule('Spacing', $g->zeroOrMore($g->charClass('[ \t\r\n]')))
            ->build();
    }

    private function recursivePeg(): string
    {
        return <<<'PEG'
Program        <- Spacing Block Spacing
Block          <- 'block' Spacing Identifier Spacing '{' Spacing Statement* Spacing '}'
Statement      <- Spacing (PrintStatement / Block)
PrintStatement <- 'print' Spacing String Spacing
Identifier     <- [a-zA-Z_] [a-zA-Z0-9_]*
String         <- '"' StringChar* '"'
StringChar     <- !'"' .
Spacing        <- [ \t\r\n]*
PEG;
    }

    private function recursiveProgramInput(): string
    {
        return <<<'TEXT'
block main {
    print "hello"

    block nested {
        print "inside"

        block deeper {
            print "deep"
        }
    }

    print "done"
}
TEXT;
    }

    private function childByNameAt(?AstNode $node, string $name, int $index): ?AstNode
    {
        if ($node === null) {
            return null;
        }

        return $node->childrenByName($name)[$index] ?? null;
    }
}
