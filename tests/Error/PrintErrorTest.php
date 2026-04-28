<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPPeg\Tests\Error;

use EmanueleCoppola\PHPPeg\Error\PrintError;
use PHPUnit\Framework\TestCase;

class PrintErrorTest extends TestCase
{
    /**
     * Verifies the print error carries the supplied message.
     */
    public function testExposesTheMessage(): void
    {
        $error = new PrintError('cannot render inserted node');

        self::assertSame('cannot render inserted node', $error->getMessage());
    }
}