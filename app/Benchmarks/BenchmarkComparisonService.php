<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

use RuntimeException;

/**
 * Loads persisted benchmark history and compares the latest two runs.
 */
class BenchmarkComparisonService
{
    /**
     * Creates the comparison service.
     */
    public function __construct(
        private readonly string $resultsDirectory,
    ) {
    }

    /**
     * Compares the latest two benchmark JSON result files.
     */
    public function compareLatest(): BenchmarkComparisonReport
    {
        $jsonFiles = glob($this->resultsDirectory . '/*.json');
        if ($jsonFiles === false || count($jsonFiles) < 2) {
            throw new RuntimeException('At least two benchmark JSON result files are required.');
        }

        sort($jsonFiles, SORT_STRING);
        $currentFile = $jsonFiles[count($jsonFiles) - 1];
        $previousFile = $jsonFiles[count($jsonFiles) - 2];

        $current = $this->readBenchmarkFile($currentFile);
        $previous = $this->readBenchmarkFile($previousFile);
        $previousBenchmarks = $this->indexBenchmarks($previous['benchmarks'] ?? []);
        $currentBenchmarks = $this->indexBenchmarks($current['benchmarks'] ?? []);

        $rows = [];
        foreach ($currentBenchmarks as $slug => $benchmark) {
            $previousBenchmark = $previousBenchmarks[$slug] ?? null;
            $rows[] = new BenchmarkComparisonRow(
                $benchmark['benchmark'] ?? $slug,
                $slug,
                $previousBenchmark === null ? null : (float) $previousBenchmark['avg_time_ms'],
                (float) $benchmark['avg_time_ms'],
                $previousBenchmark === null ? null : (float) $previousBenchmark['peak_memory_bytes'],
                (float) $benchmark['peak_memory_bytes'],
            );
        }

        return new BenchmarkComparisonReport($previousFile, $currentFile, $rows);
    }

    /**
     * Reads one persisted benchmark JSON file.
     *
     * @return array<string, mixed>
     */
    private function readBenchmarkFile(string $path): array
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException(sprintf('Unable to read benchmark file: %s', $path));
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf('Unable to decode benchmark file: %s', $path));
        }

        return $decoded;
    }

    /**
     * Indexes benchmark rows by slug.
     *
     * @param array<int, array<string, mixed>> $benchmarks
     * @return array<string, array<string, mixed>>
     */
    private function indexBenchmarks(array $benchmarks): array
    {
        $indexed = [];
        foreach ($benchmarks as $benchmark) {
            if (!isset($benchmark['slug']) || !is_string($benchmark['slug'])) {
                continue;
            }

            $indexed[$benchmark['slug']] = $benchmark;
        }

        ksort($indexed);

        return $indexed;
    }
}
