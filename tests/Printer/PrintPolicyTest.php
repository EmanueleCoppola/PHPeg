<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Printer;

use EmanueleCoppola\PHPPeg\Printer\PrintPolicy;
use PHPUnit\Framework\TestCase;

class PrintPolicyTest extends TestCase
{
    /**
     * Verifies the formatting defaults and custom values.
     */
    public function testExposesFormattingDefaults(): void
    {
        $default = new PrintPolicy();
        $custom = new PrintPolicy(indent: '  ', newline: "\r\n");

        self::assertSame('    ', $default->indent);
        self::assertSame("\n", $default->newline);
        self::assertSame('  ', $custom->indent);
        self::assertSame("\r\n", $custom->newline);
    }
}