<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Support;

use EmanueleCoppola\PHPeg\Support\AstPrinter;
use EmanueleCoppola\PHPeg\Ast\AstNodeFactory;
use PHPUnit\Framework\TestCase;

class AstPrinterTest extends TestCase
{
    /**
     * Verifies the tree printer renders readable labels.
     */
    public function testPrintsReadableTree(): void
    {
        $factory = new AstNodeFactory();
        $tree = $factory->node(
            'Block',
            [
                $factory->node(
                    'Directive',
                    [
                        $factory->token('Identifier', 'server'),
                        $factory->token('Value', 'example.com'),
                    ],
                    'server example.com',
                    ['name' => 'server'],
                ),
            ],
            "{\n    server example.com\n}",
            ['name' => 'root'],
        );
        $printed = AstPrinter::print($tree);

        self::assertStringContainsString('Block', $printed);
        self::assertStringContainsString('Identifier: server', $printed);
        self::assertStringContainsString('Value', $printed);
    }
}
