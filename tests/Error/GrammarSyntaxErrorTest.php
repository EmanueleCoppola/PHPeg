<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Error;

use EmanueleCoppola\PHPPeg\Error\GrammarSyntaxError;
use PHPUnit\Framework\TestCase;

class GrammarSyntaxErrorTest extends TestCase
{
    /**
     * Verifies the grammar syntax error formats the message and exposes coordinates.
     */
    public function testExposesGrammarSyntaxDetails(): void
    {
        $error = new GrammarSyntaxError('CleanPeg', 2, 4, 'unexpected token');

        self::assertSame('CleanPeg', $error->grammarKind());
        self::assertSame(2, $error->line());
        self::assertSame(4, $error->column());
        self::assertSame('Invalid CleanPeg syntax at line 2, column 4: unexpected token', $error->getMessage());
    }
}