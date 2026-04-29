<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Error;

use EmanueleCoppola\PHPeg\Error\AstQueryError;
use PHPUnit\Framework\TestCase;

class AstQueryErrorTest extends TestCase
{
    /**
     * Verifies the query error carries the supplied message.
     */
    public function testExposesTheMessage(): void
    {
        $error = new AstQueryError('bad selector');

        self::assertSame('bad selector', $error->getMessage());
    }
}