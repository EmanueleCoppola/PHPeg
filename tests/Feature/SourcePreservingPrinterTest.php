<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Feature;

use EmanueleCoppola\PHPeg\Ast\AstNodeFactory;
use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Document\ParsedDocument;
use PHPUnit\Framework\TestCase;

class SourcePreservingPrinterTest extends TestCase
{
    public function testUnmodifiedDocumentPrintsExactlyTheOriginalSource(): void
    {
        $document = $this->nginxDocument();

        self::assertSame($document->source(), $document->print());
    }

    public function testAppendingNodePreservesMostExistingFormatting(): void
    {
        $document = $this->nginxDocument();
        $factory = new AstNodeFactory();

        $document->query('Block[name="server"]')->first()?->appendNode(
            $factory->node('Statement', text: "\n    client_max_body_size 20m;")
        );

        $printed = $document->print();
        self::assertStringContainsString("server_name example.com;\n\n    location /api {", $printed);
        self::assertStringContainsString('client_max_body_size 20m;', $printed);
    }

    public function testRemovingNodeRemovesOnlyThatNode(): void
    {
        $document = $this->nginxDocument();
        $document->query('Directive[name="listen"]')->first()?->remove();
        $printed = $document->print();

        self::assertStringNotContainsString('listen 80;', $printed);
        self::assertStringContainsString('server_name example.com;', $printed);
    }

    public function testReplacingNodePreservesSurroundingText(): void
    {
        $document = $this->nginxDocument();
        $factory = new AstNodeFactory();
        $document->query('Identifier[text="example.com"]')->first()?->replaceWith($factory->token('Identifier', 'example.org'));

        self::assertStringContainsString('server_name example.org;', $document->print());
    }

    public function testCommentsOutsideModifiedNodesArePreserved(): void
    {
        $document = $this->nginxDocument();
        $factory = new AstNodeFactory();
        $document->query('Block[name="server"]')->first()?->appendNode(
            $factory->node('Statement', text: "\n    client_max_body_size 20m;")
        );

        self::assertStringContainsString('# top comment', $document->print());
    }

    public function testModifiedPrintedSourceCanBeReparsed(): void
    {
        $document = $this->nginxDocument();
        $factory = new AstNodeFactory();
        $document->query('Block[name="server"]')->first()?->appendNode(
            $factory->node('Statement', text: "\n    client_max_body_size 20m;")
        );

        self::assertTrue($document->validatePrintedSource()->isSuccess());
    }

    private function nginxDocument(): ParsedDocument
    {
        return $this->nginxGrammar()->parseDocument($this->nginxSource());
    }

    private function nginxGrammar(): \EmanueleCoppola\PHPeg\Grammar\Grammar
    {
        $g = GrammarBuilder::create();

        return $g->grammar('Config')
            ->rule('Config', $g->seq($g->ref('Spacing'), $g->zeroOrMore($g->ref('Statement')), $g->ref('Spacing'), $g->eof()))
            ->rule('Statement', $g->seq($g->ref('Spacing'), $g->choice($g->ref('Directive'), $g->ref('Block'))))
            ->rule('Block', $g->seq(
                $g->ref('Identifier'),
                $g->ref('Spacing'),
                $g->optional($g->ref('Value')),
                $g->ref('Spacing'),
                $g->literal('{'),
                $g->ref('Spacing'),
                $g->zeroOrMore($g->ref('Statement')),
                $g->ref('Spacing'),
                $g->literal('}'),
            ))
            ->rule('Directive', $g->seq($g->ref('Identifier'), $g->ref('Spacing'), $g->ref('ValueList'), $g->ref('Spacing'), $g->literal(';')))
            ->rule('ValueList', $g->seq($g->ref('Value'), $g->zeroOrMore($g->seq($g->ref('Spacing'), $g->ref('Value')))))
            ->rule('Value', $g->choice($g->ref('Url'), $g->ref('Path'), $g->ref('Identifier'), $g->ref('Number'), $g->ref('String')))
            ->rule('Identifier', $g->seq($g->charClass('[a-zA-Z_]'), $g->zeroOrMore($g->charClass('[a-zA-Z0-9_\\.-]'))))
            ->rule('Path', $g->seq($g->literal('/'), $g->zeroOrMore($g->charClass('[a-zA-Z0-9_./-]'))))
            ->rule('Url', $g->seq($g->literal('http://'), $g->oneOrMore($g->charClass('[a-zA-Z0-9_./:-]'))))
            ->rule('Number', $g->oneOrMore($g->charClass('[0-9]')))
            ->rule('String', $g->seq($g->literal('"'), $g->zeroOrMore($g->seq($g->not($g->literal('"')), $g->any())), $g->literal('"')))
            ->rule('Comment', $g->seq($g->literal('#'), $g->zeroOrMore($g->seq($g->not($g->literal("\n")), $g->any())), $g->choice($g->literal("\n"), $g->eof())))
            ->rule('Spacing', $g->zeroOrMore($g->choice($g->charClass('[ \t\r\n]'), $g->ref('Comment'))))
            ->build();
    }

    private function nginxSource(): string
    {
        return <<<'TEXT'
# top comment
server {
    listen 80;
    server_name example.com;

    location /api {
        proxy_pass http://backend;
    }
}
TEXT;
    }
}
