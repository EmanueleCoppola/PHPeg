<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Parser;

use EmanueleCoppola\PHPPeg\Parser\InputBuffer;
use PHPUnit\Framework\TestCase;

class InputBufferTest extends TestCase
{
    /**
     * Verifies read access and position helpers.
     */
    public function testExposesInputUtilities(): void
    {
        $buffer = new InputBuffer("one\ntwo\nthree");

        self::assertSame("one\ntwo\nthree", $buffer->text());
        self::assertSame(13, $buffer->length());
        self::assertSame('one', $buffer->slice(0, 3));
        self::assertSame('t', $buffer->charAt(4));
        self::assertNull($buffer->charAt(99));
        self::assertSame(['line' => 2, 'column' => 1], $buffer->lineAndColumn(4));
        self::assertSame('"one\\ntwo\\n"', $buffer->snippet(4, 4));
    }
}
