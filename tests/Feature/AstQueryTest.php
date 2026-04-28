<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Feature;

use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPPeg\Document\ParsedDocument;
use EmanueleCoppola\PHPPeg\Error\AstQueryError;
use PHPUnit\Framework\TestCase;

class AstQueryTest extends TestCase
{
    public function testSelectsNodesByRuleName(): void
    {
        $document = $this->recursiveLanguageDocument();

        self::assertSame(2, $document->query('Block')->count());
    }

    public function testSelectsDescendants(): void
    {
        $document = $this->recursiveLanguageDocument();

        self::assertSame(3, $document->query('Block PrintStatement')->count());
    }

    public function testSelectsDirectChildren(): void
    {
        $document = $this->recursiveLanguageDocument();

        self::assertSame(3, $document->query('Block[name="main"] > Statement')->count());
    }

    public function testSelectsByTextAttribute(): void
    {
        $document = $this->recursiveLanguageDocument();

        self::assertSame(1, $document->query('Identifier[text="nested"]')->count());
    }

    public function testSelectsFirstChild(): void
    {
        $document = $this->recursiveLanguageDocument();
        $first = $document->query('Block[name="main"] > Statement:first')->first();

        self::assertNotNull($first);
        self::assertStringContainsString('print "hello"', $first->text());
    }

    public function testSelectsLastChild(): void
    {
        $document = $this->recursiveLanguageDocument();
        $last = $document->query('Block[name="main"] > Statement:last')->first();

        self::assertNotNull($last);
        self::assertStringContainsString('print "done"', $last->text());
    }

    public function testEmptyQueryReturnsEmptyCollection(): void
    {
        self::assertTrue($this->recursiveLanguageDocument()->query('MissingNode')->isEmpty());
    }

    public function testInvalidSelectorThrowsClearError(): void
    {
        $this->expectException(AstQueryError::class);

        $this->recursiveLanguageDocument()->query('Block[');
    }

    private function recursiveLanguageDocument(): ParsedDocument
    {
        $g = GrammarBuilder::create();
        $grammar = $g->grammar('Program')
            ->rule('Program', $g->seq($g->ref('Spacing'), $g->ref('Block'), $g->ref('Spacing')))
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
            ->rule('Statement', $g->seq($g->ref('Spacing'), $g->choice($g->ref('PrintStatement'), $g->ref('Block'))))
            ->rule('PrintStatement', $g->seq($g->literal('print'), $g->ref('Spacing'), $g->ref('String'), $g->ref('Spacing')))
            ->rule('Identifier', $g->seq($g->charClass('[a-zA-Z_]'), $g->zeroOrMore($g->charClass('[a-zA-Z0-9_]'))))
            ->rule('String', $g->seq($g->literal('"'), $g->zeroOrMore($g->ref('StringChar')), $g->literal('"')))
            ->rule('StringChar', $g->seq($g->not($g->literal('"')), $g->any()))
            ->rule('Spacing', $g->zeroOrMore($g->charClass('[ \t\r\n]')))
            ->build();

        return $grammar->parseDocument(<<<'TEXT'
block main {
    print "hello"

    block nested {
        print "inside"
    }

    print "done"
}
TEXT);
    }
}
