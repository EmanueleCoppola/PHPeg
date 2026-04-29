<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Feature;

use EmanueleCoppola\PHPeg\Ast\AstNodeFactory;
use EmanueleCoppola\PHPeg\Builder\GrammarBuilder;
use EmanueleCoppola\PHPeg\Document\ParsedDocument;
use PHPUnit\Framework\TestCase;

class NginxLikeConfigEditTest extends TestCase
{
    public function testParseSimpleServerBlock(): void
    {
        self::assertSame('Config', $this->document()->root()->name());
    }

    public function testQueryServerBlock(): void
    {
        self::assertSame(1, $this->document()->query('Block[name="server"]')->count());
    }

    public function testQueryExistingDirectiveByName(): void
    {
        self::assertSame(1, $this->document()->query('Directive[name="listen"]')->count());
    }

    public function testAddMissingDirective(): void
    {
        $document = $this->document();
        $factory = new AstNodeFactory();
        $server = $document->query('Block[name="server"]')->first();

        if ($server !== null && $server->query('Directive[name="client_max_body_size"]')->isEmpty()) {
            $server->appendNode($factory->node('Statement', text: "\n    client_max_body_size 20m;"));
        }

        self::assertStringContainsString('client_max_body_size 20m;', $document->print());
    }

    public function testAvoidAddingDuplicateDirectiveIfAlreadyPresent(): void
    {
        $document = $this->document();
        $factory = new AstNodeFactory();
        $server = $document->query('Block[name="server"]')->first();

        if ($server !== null && $server->query('Directive[name="listen"]')->isEmpty()) {
            $server->appendNode($factory->node('Statement', text: "\n    listen 80;"));
        }

        self::assertSame(1, substr_count($document->print(), 'listen 80;'));
    }

    public function testPrintModifiedConfig(): void
    {
        $document = $this->document();
        $factory = new AstNodeFactory();
        $document->query('Block[name="server"]')->first()?->appendNode(
            $factory->node('Statement', text: "\n    client_max_body_size 20m;")
        );

        self::assertStringContainsString('client_max_body_size 20m;', $document->print());
    }

    public function testReparseModifiedConfigSuccessfully(): void
    {
        $document = $this->document();
        $factory = new AstNodeFactory();
        $document->query('Block[name="server"]')->first()?->appendNode(
            $factory->node('Statement', text: "\n    client_max_body_size 20m;")
        );

        self::assertTrue($document->validate()->isSuccess());
    }

    private function document(): ParsedDocument
    {
        return $this->grammar()->parseDocument(<<<'TEXT'
server {
    listen 80;
    server_name example.com;

    location /api {
        proxy_pass http://backend;
    }
}
TEXT);
    }

    private function grammar(): \EmanueleCoppola\PHPeg\Grammar\Grammar
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
}
