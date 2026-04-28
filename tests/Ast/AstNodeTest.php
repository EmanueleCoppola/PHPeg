<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Ast;

use EmanueleCoppola\PHPPeg\Ast\AstNode;
use EmanueleCoppola\PHPPeg\Ast\AstNodeFactory;
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
}
