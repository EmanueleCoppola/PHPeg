<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\Parser;

use RuntimeException;

/**
 * Stores parse-time performance and diagnostics options.
 */
class ParserOptions
{
    /**
     * Creates a parser option set.
     */
    public function __construct(
        private readonly bool $memoizationEnabled = true,
        private readonly bool $memoryOptimized = false,
        private readonly bool $fastMode = false,
        private readonly ?int $maxCacheEntries = null,
        private readonly bool $optimizeErrors = false,
        private readonly bool $reuseEmptyMatches = false,
    ) {
        if ($maxCacheEntries !== null && $maxCacheEntries < 0) {
            throw new RuntimeException('maxCacheEntries must be greater than or equal to zero.');
        }
    }

    /**
     * Returns the default compatibility-focused parser options.
     */
    public static function defaults(): self
    {
        return new self();
    }

    /**
     * Returns a speed-oriented parser option preset.
     */
    public static function fast(): self
    {
        return new self(
            memoizationEnabled: true,
            memoryOptimized: false,
            fastMode: true,
            maxCacheEntries: null,
            optimizeErrors: true,
            reuseEmptyMatches: true,
        );
    }

    /**
     * Returns a memory-oriented parser option preset.
     */
    public static function memoryOptimized(): self
    {
        return new self(
            memoizationEnabled: false,
            memoryOptimized: true,
            fastMode: false,
            maxCacheEntries: 0,
            optimizeErrors: false,
            reuseEmptyMatches: false,
        );
    }

    /**
     * Returns whether rule memoization is enabled.
     */
    public function memoizationEnabled(): bool
    {
        return $this->memoizationEnabled;
    }

    /**
     * Returns whether the memory-optimized preset is active.
     */
    public function memoryOptimizedEnabled(): bool
    {
        return $this->memoryOptimized;
    }

    /**
     * Returns whether the speed-oriented preset is active.
     */
    public function fastModeEnabled(): bool
    {
        return $this->fastMode;
    }

    /**
     * Returns the maximum number of memoized entries, or null when unbounded.
     */
    public function maxCacheEntries(): ?int
    {
        return $this->maxCacheEntries;
    }

    /**
     * Returns whether error tracking is optimized for successful parses.
     */
    public function optimizeErrors(): bool
    {
        return $this->optimizeErrors;
    }

    /**
     * Returns whether zero-width matches should be cached by offset.
     */
    public function reuseEmptyMatches(): bool
    {
        return $this->reuseEmptyMatches;
    }

    /**
     * Returns a copy with memoization toggled.
     */
    public function withMemoization(bool $enabled): self
    {
        return new self(
            memoizationEnabled: $enabled,
            memoryOptimized: $this->memoryOptimized,
            fastMode: $this->fastMode,
            maxCacheEntries: $this->maxCacheEntries,
            optimizeErrors: $this->optimizeErrors,
            reuseEmptyMatches: $this->reuseEmptyMatches,
        );
    }

    /**
     * Returns a copy with the memory-oriented mode toggled.
     */
    public function withMemoryOptimized(bool $enabled): self
    {
        return new self(
            memoizationEnabled: $this->memoizationEnabled,
            memoryOptimized: $enabled,
            fastMode: $this->fastMode,
            maxCacheEntries: $this->maxCacheEntries,
            optimizeErrors: $this->optimizeErrors,
            reuseEmptyMatches: $this->reuseEmptyMatches,
        );
    }

    /**
     * Returns a copy with the speed-oriented mode toggled.
     */
    public function withFastMode(bool $enabled): self
    {
        return new self(
            memoizationEnabled: $this->memoizationEnabled,
            memoryOptimized: $this->memoryOptimized,
            fastMode: $enabled,
            maxCacheEntries: $this->maxCacheEntries,
            optimizeErrors: $this->optimizeErrors,
            reuseEmptyMatches: $this->reuseEmptyMatches,
        );
    }

    /**
     * Returns a copy with an updated memoization cache limit.
     */
    public function withMaxCacheEntries(?int $maxCacheEntries): self
    {
        return new self(
            memoizationEnabled: $this->memoizationEnabled,
            memoryOptimized: $this->memoryOptimized,
            fastMode: $this->fastMode,
            maxCacheEntries: $maxCacheEntries,
            optimizeErrors: $this->optimizeErrors,
            reuseEmptyMatches: $this->reuseEmptyMatches,
        );
    }

    /**
     * Returns a copy with optimized error tracking toggled.
     */
    public function withOptimizeErrors(bool $enabled): self
    {
        return new self(
            memoizationEnabled: $this->memoizationEnabled,
            memoryOptimized: $this->memoryOptimized,
            fastMode: $this->fastMode,
            maxCacheEntries: $this->maxCacheEntries,
            optimizeErrors: $enabled,
            reuseEmptyMatches: $this->reuseEmptyMatches,
        );
    }

    /**
     * Returns a copy with zero-width match reuse toggled.
     */
    public function withReuseEmptyMatches(bool $enabled): self
    {
        return new self(
            memoizationEnabled: $this->memoizationEnabled,
            memoryOptimized: $this->memoryOptimized,
            fastMode: $this->fastMode,
            maxCacheEntries: $this->maxCacheEntries,
            optimizeErrors: $this->optimizeErrors,
            reuseEmptyMatches: $enabled,
        );
    }

    /**
     * Returns a human-readable summary for diagnostics and benchmarks.
     *
     * @return array<string, bool|int|null>
     */
    public function toArray(): array
    {
        return [
            'memoization' => $this->memoizationEnabled,
            'memoryOptimized' => $this->memoryOptimized,
            'fastMode' => $this->fastMode,
            'maxCacheEntries' => $this->maxCacheEntries,
            'optimizeErrors' => $this->optimizeErrors,
            'reuseEmptyMatches' => $this->reuseEmptyMatches,
        ];
    }
}
