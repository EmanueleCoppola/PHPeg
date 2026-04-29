<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

/**
 * Represents one persisted benchmark suite run.
 */
class BenchmarkRunReport
{
    /**
     * @param list<BenchmarkResult> $results
     */
    public function __construct(
        private readonly BenchmarkOptions $options,
        private readonly array $results,
        private readonly string $jsonFile,
        private readonly string $csvFile,
    ) {
    }

    /**
     * Returns the normalized options used for this run.
     */
    public function options(): BenchmarkOptions
    {
        return $this->options;
    }

    /**
     * Returns the benchmark results.
     *
     * @return list<BenchmarkResult>
     */
    public function results(): array
    {
        return $this->results;
    }

    /**
     * Returns the persisted JSON result file path.
     */
    public function jsonFile(): string
    {
        return $this->jsonFile;
    }

    /**
     * Returns the append-only CSV history path.
     */
    public function csvFile(): string
    {
        return $this->csvFile;
    }

    /**
     * Returns whether any benchmark failed.
     */
    public function hasFailures(): bool
    {
        foreach ($this->results as $result) {
            if (!$result->isSuccess()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Converts the report into a serializable array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'scale' => $this->options->scale(),
            'iterations' => $this->options->iterations(),
            'filter' => $this->options->filter(),
            'modes' => $this->options->modes(),
            'results_file' => $this->jsonFile,
            'history_csv' => $this->csvFile,
            'benchmarks' => array_map(static fn (BenchmarkResult $result): array => $result->toArray(), $this->results),
        ];
    }
}
