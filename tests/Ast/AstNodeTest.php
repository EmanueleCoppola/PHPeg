<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Ast;

use EmanueleCoppola\PHPeg\Ast\AstNode;
use EmanueleCoppola\PHPeg\Ast\AstNodeFactory;
use PHPUnit\Framework\TestCase;

class AstNodeTest extends TestCase
{
    /**
     * Verifies tree accessors, attributes, and descendant traversal.
     */
    public function testExposesTreeInformation(): void
    {
        $root = $this->tree();
        $directive = $root->firstChild('Directive');

        self::assertSame('Block', $root->name());
        self::assertSame('root', $root->attribute('name'));
        self::assertCount(1, $root->childrenByName('Directive'));
        self::assertCount(1, $root->directChildren());
        self::assertCount(4, $root->descendantsAndSelf());
        self::assertNotNull($directive);
        self::assertSame('server', $directive->attribute('name'));
        self::assertSame('Directive', $directive->attribute('type'));
        self::assertSame('server example.com', trim($directive->text()));
        self::assertSame('server', $directive->firstChild('Identifier')?->text());
    }

    /**
     * Verifies child insertion and replacement helpers.
     */
    public function testSupportsMutatingTheTree(): void
    {
        $factory = new AstNodeFactory();
        $root = $this->tree();
        $directive = $root->firstChild('Directive');

        self::assertNotNull($directive);
        self::assertTrue($root->canContainChildren());

        $root->prependNode($factory->token('Directive', 'worker_processes 2;'));
        $root->appendNode($factory->token('Directive', 'server_tokens off;'));
        self::assertCount(3, $root->childrenByName('Directive'));

        $directive->setRenderText('listen 443;');
        $directive->setAttributes(['name' => 'listen']);
        self::assertSame('listen', $directive->attribute('name'));
        self::assertSame('listen 443;', $directive->text());

        $directive->replaceWith($factory->token('Directive', 'listen 80;'));
        self::assertSame('listen 80;', trim($root->childrenByName('Directive')[1]->text()));

        $root->firstChild('Directive')?->remove();
        self::assertCount(2, $root->childrenByName('Directive'));
    }

    /**
     * Verifies source-preserving child snapshots remain correct after lazy initialization.
     */
    public function testRetainsOriginalChildSnapshotsAcrossMutations(): void
    {
        $factory = new AstNodeFactory();
        $root = $this->originalTree();
        $originalDirective = $root->firstChild('Directive');

        self::assertNotNull($originalDirective);
        self::assertSame([$originalDirective], $root->originalChildren());
        self::assertSame($originalDirective, $root->slotNodes()[0] ?? null);

        $replacement = $factory->token('Directive', 'listen 80;');
        $originalDirective->replaceWith($replacement);

        self::assertSame([$originalDirective], $root->originalChildren());
        self::assertSame($replacement, $root->slotNodes()[0] ?? null);
    }

    /**
     * Builds a small editable AST used by the node tests.
     */
    private function tree(): AstNode
    {
        $factory = new AstNodeFactory();
        $identifier = $factory->token('Identifier', 'server');
        $value = $factory->token('Value', 'example.com');
        $directive = $factory->node('Directive', [$identifier, $value], 'server example.com', ['name' => 'server']);

        return $factory->node('Block', [$directive], "{\n    server example.com\n}", ['name' => 'root']);
    }

    /**
     * Builds a parsed-like AST whose nodes are marked as original.
     */
    private function originalTree(): AstNode
    {
        $identifier = new AstNode('Identifier', 'server', 6, 12, isOriginal: true);
        $value = new AstNode('Value', 'example.com', 13, 24, isOriginal: true);
        $directive = new AstNode('Directive', 'server example.com', 6, 24, [$identifier, $value], ['name' => 'server'], true);

        return new AstNode('Block', "{\n    server example.com\n}", 0, 27, [$directive], ['name' => 'root'], true);
    }
}
