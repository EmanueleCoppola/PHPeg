<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

/**
 * Stores the outcome of a single benchmark case run.
 */
class BenchmarkResult
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        private readonly string $name,
        private readonly string $slug,
        private readonly int $iterations,
        private readonly int $inputSizeBytes,
        private readonly float $totalTimeMs,
        private readonly float $averageTimeMs,
        private readonly float $minTimeMs,
        private readonly float $maxTimeMs,
        private readonly int $peakMemoryBytes,
        private readonly int $memoryBeforeBytes,
        private readonly int $memoryAfterBytes,
        private readonly int $memoryDeltaBytes,
        private readonly bool $success,
        private readonly ?string $errorMessage,
        private readonly array $extra = [],
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
     * Returns the requested iteration count.
     */
    public function iterations(): int
    {
        return $this->iterations;
    }

    /**
     * Returns the generated input size in bytes.
     */
    public function inputSizeBytes(): int
    {
        return $this->inputSizeBytes;
    }

    /**
     * Returns total parsing time across all iterations.
     */
    public function totalTimeMs(): float
    {
        return $this->totalTimeMs;
    }

    /**
     * Returns the average iteration time in milliseconds.
     */
    public function averageTimeMs(): float
    {
        return $this->averageTimeMs;
    }

    /**
     * Returns the minimum iteration time in milliseconds.
     */
    public function minTimeMs(): float
    {
        return $this->minTimeMs;
    }

    /**
     * Returns the maximum iteration time in milliseconds.
     */
    public function maxTimeMs(): float
    {
        return $this->maxTimeMs;
    }

    /**
     * Returns the highest observed peak memory in bytes.
     */
    public function peakMemoryBytes(): int
    {
        return $this->peakMemoryBytes;
    }

    /**
     * Returns the average memory usage before parsing in bytes.
     */
    public function memoryBeforeBytes(): int
    {
        return $this->memoryBeforeBytes;
    }

    /**
     * Returns the average memory usage after parsing in bytes.
     */
    public function memoryAfterBytes(): int
    {
        return $this->memoryAfterBytes;
    }

    /**
     * Returns the average memory delta in bytes.
     */
    public function memoryDeltaBytes(): int
    {
        return $this->memoryDeltaBytes;
    }

    /**
     * Returns whether the benchmark completed successfully.
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Returns the failure message when the benchmark did not succeed.
     */
    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Returns additional benchmark metadata.
     *
     * @return array<string, mixed>
     */
    public function extra(): array
    {
        return $this->extra;
    }

    /**
     * Converts the result into a serializable array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'benchmark' => $this->name,
            'slug' => $this->slug,
            'iterations' => $this->iterations,
            'input_size_bytes' => $this->inputSizeBytes,
            'total_time_ms' => $this->totalTimeMs,
            'avg_time_ms' => $this->averageTimeMs,
            'min_time_ms' => $this->minTimeMs,
            'max_time_ms' => $this->maxTimeMs,
            'peak_memory_bytes' => $this->peakMemoryBytes,
            'memory_before_bytes' => $this->memoryBeforeBytes,
            'memory_after_bytes' => $this->memoryAfterBytes,
            'memory_delta_bytes' => $this->memoryDeltaBytes,
            'status' => $this->success ? 'ok' : 'failed',
            'error' => $this->errorMessage,
            'extra' => $this->extra,
        ];
    }
}
