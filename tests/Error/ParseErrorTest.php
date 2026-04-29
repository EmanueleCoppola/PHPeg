<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Error;

use EmanueleCoppola\PHPeg\Error\ParseError;
use PHPUnit\Framework\TestCase;

class ParseErrorTest extends TestCase
{
    /**
     * Verifies parse errors expose position and message details.
     */
    public function testExposesParseFailureDetails(): void
    {
        $error = new ParseError(3, 2, 4, ['"a"', 'EOF'], '"b"');

        self::assertSame(3, $error->offset());
        self::assertSame(2, $error->line());
        self::assertSame(4, $error->column());
        self::assertSame(['"a"', 'EOF'], $error->expected());
        self::assertSame('Parse error at line 2, column 4 (offset 3). Expected: "a", EOF. Near: "b"', $error->message());
    }

    /**
     * Verifies left-recursion parse errors use a specific message.
     */
    public function testBuildsLeftRecursionErrors(): void
    {
        $error = ParseError::leftRecursion('Start', 5, 1, 6, '"a"');

        self::assertSame('Left-recursive rule detected: Start at line 1, column 6 (offset 5). Near: "a"', $error->message());
    }
}