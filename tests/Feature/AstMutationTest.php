<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Feature;

use EmanueleCoppola\PHPPeg\Ast\AstNodeFactory;
use EmanueleCoppola\PHPPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPPeg\Document\ParsedDocument;
use EmanueleCoppola\PHPPeg\Error\AstMutationError;
use PHPUnit\Framework\TestCase;

class AstMutationTest extends TestCase
{
    public function testAppendNodeToSelectedBlock(): void
    {
        $document = $this->recursiveLanguageDocument();
        $factory = new AstNodeFactory();

        $document->query('Block[name="nested"]')->first()?->appendNode(
            $factory->node('Statement', text: '        print "inserted"' . "\n")
        );

        self::assertStringContainsString('print "inserted"', $document->print());
    }

    public function testPrependNodeToSelectedBlock(): void
    {
        $document = $this->recursiveLanguageDocument();
        $factory = new AstNodeFactory();

        $document->query('Block[name="nested"]')->first()?->prependNode(
            $factory->node('Statement', text: '        print "first"' . "\n")
        );

        self::assertStringContainsString('print "first"', $document->print());
    }

    public function testInsertNodeBeforeAnotherNode(): void
    {
        $document = $this->recursiveLanguageDocument();
        $factory = new AstNodeFactory();

        $document->query('Block > Statement:last')->first()?->before(
            $factory->node('Statement', text: '    print "before done"' . "\n\n")
        );

        self::assertStringContainsString('print "before done"', $document->print());
    }

    public function testInsertNodeAfterAnotherNode(): void
    {
        $document = $this->recursiveLanguageDocument();
        $factory = new AstNodeFactory();

        $document->query('Block > Statement:first')->first()?->after(
            $factory->node('Statement', text: '    print "after hello"' . "\n\n")
        );

        self::assertStringContainsString('print "after hello"', $document->print());
    }

    public function testReplaceNode(): void
    {
        $document = $this->recursiveLanguageDocument();
        $factory = new AstNodeFactory();

        $document->query('Identifier[text="nested"]')->first()?->replaceWith($factory->token('Identifier', 'renamed'));

        self::assertStringContainsString('block renamed {', $document->print());
    }

    public function testRemoveNode(): void
    {
        $document = $this->recursiveLanguageDocument();

        $document->query('Block > Statement:last')->first()?->remove();

        self::assertStringNotContainsString('print "done"', $document->print());
    }

    public function testCollectionMutationAppliesToMultipleSelectedNodes(): void
    {
        $document = $this->recursiveLanguageDocument();
        $factory = new AstNodeFactory();

        $document->query('Block')->appendNode($factory->node('Statement', text: '    print "shared"' . "\n"));

        self::assertGreaterThanOrEqual(2, substr_count($document->print(), 'print "shared"'));
    }

    public function testCannotInsertChildrenIntoLeafNodes(): void
    {
        $this->expectException(AstMutationError::class);

        $document = $this->recursiveLanguageDocument();
        $factory = new AstNodeFactory();

        $document->query('Identifier[text="main"]')->first()?->appendNode($factory->token('Identifier', 'x'));
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
