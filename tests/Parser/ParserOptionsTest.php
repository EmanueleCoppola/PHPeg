<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Tests\Parser;

use EmanueleCoppola\PHPeg\Parser\ParserOptions;
use PHPUnit\Framework\TestCase;

class ParserOptionsTest extends TestCase
{
    /**
     * Verifies the default parser options preserve compatibility behavior.
     */
    public function testDefaultsExposeCompatibilitySettings(): void
    {
        $options = ParserOptions::defaults();

        self::assertTrue($options->memoizationEnabled());
        self::assertFalse($options->memoryOptimizedEnabled());
        self::assertFalse($options->fastModeEnabled());
        self::assertNull($options->maxCacheEntries());
        self::assertFalse($options->optimizeErrors());
        self::assertFalse($options->reuseEmptyMatches());
    }

    /**
     * Verifies the speed-oriented preset enables the documented tradeoffs.
     */
    public function testFastPresetEnablesSpeedTradeoffs(): void
    {
        $options = ParserOptions::fast();

        self::assertTrue($options->memoizationEnabled());
        self::assertTrue($options->fastModeEnabled());
        self::assertTrue($options->optimizeErrors());
        self::assertTrue($options->reuseEmptyMatches());
    }

    /**
     * Verifies the memory-oriented preset disables cache-heavy features.
     */
    public function testMemoryPresetDisablesCacheHeavyFeatures(): void
    {
        $options = ParserOptions::memoryOptimized();

        self::assertFalse($options->memoizationEnabled());
        self::assertTrue($options->memoryOptimizedEnabled());
        self::assertSame(0, $options->maxCacheEntries());
        self::assertFalse($options->reuseEmptyMatches());
    }
}
