<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Ast;

use EmanueleCoppola\PHPeg\Ast\AstNodeFactory;
use PHPUnit\Framework\TestCase;

class AstNodeFactoryTest extends TestCase
{
    /**
     * Verifies the factory creates usable nodes and tokens.
     */
    public function testCreatesNodesAndTokens(): void
    {
        $factory = new AstNodeFactory();
        $token = $factory->token('Identifier', 'server');
        $node = $factory->node('Directive', [$token], 'server');

        self::assertSame('Identifier', $token->name());
        self::assertSame('server', $token->text());
        self::assertTrue($token->isInserted());
        self::assertSame('Directive', $node->name());
        self::assertSame('server', $node->text());
        self::assertCount(1, $node->children());
    }
}