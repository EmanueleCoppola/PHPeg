<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Error;

use EmanueleCoppola\PHPeg\Error\LeftRecursionException;
use PHPUnit\Framework\TestCase;

class LeftRecursionExceptionTest extends TestCase
{
    /**
     * Verifies the exception exposes the recursion rule and offset.
     */
    public function testExposesRuleAndOffset(): void
    {
        $error = new LeftRecursionException('Start', 7);

        self::assertSame('Start', $error->ruleName());
        self::assertSame(7, $error->offset());
        self::assertSame('Left-recursive rule detected: Start', $error->getMessage());
    }
}