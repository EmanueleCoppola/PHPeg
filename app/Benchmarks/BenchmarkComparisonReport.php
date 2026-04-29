<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

/**
 * Represents a comparison between the two most recent benchmark runs.
 */
class BenchmarkComparisonReport
{
    /**
     * @param list<BenchmarkComparisonRow> $rows
     */
    public function __construct(
        private readonly string $previousFile,
        private readonly string $currentFile,
        private readonly array $rows,
    ) {
    }

    /**
     * Returns the previous benchmark file path.
     */
    public function previousFile(): string
    {
        return $this->previousFile;
    }

    /**
     * Returns the current benchmark file path.
     */
    public function currentFile(): string
    {
        return $this->currentFile;
    }

    /**
     * Returns the comparison rows.
     *
     * @return list<BenchmarkComparisonRow>
     */
    public function rows(): array
    {
        return $this->rows;
    }
}
