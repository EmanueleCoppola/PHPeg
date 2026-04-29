<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

/**
 * Represents one benchmark-to-benchmark comparison row.
 */
class BenchmarkComparisonRow
{
    /**
     * Creates the comparison row.
     */
    public function __construct(
        private readonly string $name,
        private readonly string $slug,
        private readonly string $mode,
        private readonly ?float $previousAverageTimeMs,
        private readonly float $currentAverageTimeMs,
        private readonly ?float $previousPeakMemoryBytes,
        private readonly float $currentPeakMemoryBytes,
    ) {
    }

    /**
     * Returns the benchmark name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the benchmark slug.
     */
    public function slug(): string
    {
        return $this->slug;
    }

    /**
     * Returns the parser mode label.
     */
    public function mode(): string
    {
        return $this->mode;
    }

    /**
     * Returns the previous average time in milliseconds when available.
     */
    public function previousAverageTimeMs(): ?float
    {
        return $this->previousAverageTimeMs;
    }

    /**
     * Returns the current average time in milliseconds.
     */
    public function currentAverageTimeMs(): float
    {
        return $this->currentAverageTimeMs;
    }

    /**
     * Returns the previous peak memory in bytes when available.
     */
    public function previousPeakMemoryBytes(): ?float
    {
        return $this->previousPeakMemoryBytes;
    }

    /**
     * Returns the current peak memory in bytes.
     */
    public function currentPeakMemoryBytes(): float
    {
        return $this->currentPeakMemoryBytes;
    }
}
