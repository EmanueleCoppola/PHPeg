<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Result;

use EmanueleCoppola\PHPeg\Result\MatchResult;
use PHPUnit\Framework\TestCase;

class MatchResultTest extends TestCase
{
    /**
     * Verifies match result accessors.
     */
    public function testExposesOffsetsAndNodes(): void
    {
        $result = new MatchResult(1, 4, []);
        $empty = MatchResult::empty(2);

        self::assertSame(1, $result->startOffset());
        self::assertSame(4, $result->endOffset());
        self::assertSame([], $result->nodes());
        self::assertSame(2, $empty->startOffset());
        self::assertSame(2, $empty->endOffset());
    }
}