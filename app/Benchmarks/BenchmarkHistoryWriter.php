<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

use DateTimeImmutable;
use RuntimeException;

/**
 * Persists benchmark runs to JSON snapshots and append-only CSV history.
 */
class BenchmarkHistoryWriter
{
    public function __construct(
        private readonly string $repositoryRoot,
        private readonly string $resultsDirectory,
    ) {
    }

    /**
     * Writes the benchmark run to disk.
     *
     * @param list<BenchmarkResult> $results
     * @return array{json_file:string,csv_file:string}
     */
    public function write(string $scale, int $iterations, array $modes, array $results): array
    {
        $this->ensureResultsDirectory();

        $timestamp = new DateTimeImmutable();
        $metadata = $this->runtimeMetadata();
        $jsonPath = $this->uniqueJsonPath($timestamp);
        $csvPath = $this->resultsDirectory . '/history.csv';

        $payload = [
            'timestamp' => $timestamp->format(DATE_ATOM),
            'git_commit' => $metadata['git_commit'],
            'git_branch' => $metadata['git_branch'],
            'git_dirty' => $metadata['git_dirty'],
            'php_version' => PHP_VERSION,
            'platform' => $metadata['platform'],
            'scale' => $scale,
            'iterations' => $iterations,
            'modes' => $modes,
            'benchmarks' => array_map(static fn (BenchmarkResult $result): array => $result->toArray(), $results),
        ];

        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            throw new RuntimeException('Unable to encode benchmark results as JSON.');
        }

        if (file_put_contents($jsonPath, $encoded . PHP_EOL) === false) {
            throw new RuntimeException(sprintf('Unable to write benchmark result file: %s', $jsonPath));
        }

        $this->appendCsv($csvPath, $timestamp, $scale, $iterations, $metadata, $results);

        return [
            'json_file' => $jsonPath,
            'csv_file' => $csvPath,
        ];
    }

    /**
     * Ensures the results directory exists.
     */
    private function ensureResultsDirectory(): void
    {
        if (is_dir($this->resultsDirectory)) {
            return;
        }

        if (!mkdir($concurrentDirectory = $this->resultsDirectory, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Unable to create benchmark results directory: %s', $this->resultsDirectory));
        }
    }

    /**
     * Returns a unique JSON output path for the current run.
     */
    private function uniqueJsonPath(DateTimeImmutable $timestamp): string
    {
        $baseName = $timestamp->format('Y-m-d_H-i-s_u');
        $path = $this->resultsDirectory . '/' . $baseName . '.json';
        $suffix = 1;

        while (is_file($path)) {
            $path = sprintf('%s/%s_%d.json', $this->resultsDirectory, $baseName, $suffix);
            $suffix++;
        }

        return $path;
    }

    /**
     * Returns git and platform metadata for the current repository.
     *
     * @return array{git_commit:string|null,git_branch:string|null,git_dirty:string|null,platform:string}
     */
    private function runtimeMetadata(): array
    {
        $commit = $this->gitCommand('git rev-parse HEAD');
        $branch = $this->gitCommand('git rev-parse --abbrev-ref HEAD');
        $dirtyOutput = $this->gitCommand('git status --porcelain');

        return [
            'git_commit' => $commit,
            'git_branch' => $branch,
            'git_dirty' => $dirtyOutput === null ? null : ($dirtyOutput === '' ? 'clean' : 'dirty'),
            'platform' => sprintf('%s %s %s', php_uname('s'), php_uname('r'), php_uname('m')),
        ];
    }

    /**
     * Executes a git command inside the repository and returns trimmed output.
     */
    private function gitCommand(string $command): ?string
    {
        $fullCommand = sprintf(
            'cd %s >/dev/null 2>&1 && %s 2>/dev/null',
            escapeshellarg($this->repositoryRoot),
            $command,
        );

        $output = shell_exec($fullCommand);
        if ($output === null) {
            return null;
        }

        return trim($output);
    }

    /**
     * Appends benchmark rows to the CSV history file.
     *
     * @param array{git_commit:string|null,git_branch:string|null,git_dirty:string|null,platform:string} $metadata
     * @param list<BenchmarkResult> $results
     */
    private function appendCsv(string $csvPath, DateTimeImmutable $timestamp, string $scale, int $iterations, array $metadata, array $results): void
    {
        $handle = fopen($csvPath, is_file($csvPath) ? 'ab' : 'wb');
        if ($handle === false) {
            throw new RuntimeException(sprintf('Unable to open benchmark history CSV: %s', $csvPath));
        }

        if (ftell($handle) === 0) {
            fputcsv($handle, [
                'timestamp',
                'git_commit',
                'git_branch',
                'git_dirty',
                'php_version',
                'platform',
                'scale',
                'iterations',
                'benchmark',
                'slug',
                'mode',
                'input_size_bytes',
                'avg_time_ms',
                'min_time_ms',
                'max_time_ms',
                'peak_memory_bytes',
                'memory_delta_bytes',
                'parser_options',
                'tradeoff',
                'status',
                'error',
            ], ',', '"', '\\');
        }

        foreach ($results as $result) {
            $extra = $result->extra();
            fputcsv($handle, [
                $timestamp->format(DATE_ATOM),
                $metadata['git_commit'],
                $metadata['git_branch'],
                $metadata['git_dirty'],
                PHP_VERSION,
                $metadata['platform'],
                $scale,
                $iterations,
                $result->name(),
                $result->slug(),
                is_string($extra['mode'] ?? null) ? $extra['mode'] : '',
                $result->inputSizeBytes(),
                number_format($result->averageTimeMs(), 4, '.', ''),
                number_format($result->minTimeMs(), 4, '.', ''),
                number_format($result->maxTimeMs(), 4, '.', ''),
                $result->peakMemoryBytes(),
                $result->memoryDeltaBytes(),
                json_encode($extra['parser_options'] ?? [], JSON_UNESCAPED_SLASHES),
                is_string($extra['tradeoff'] ?? null) ? $extra['tradeoff'] : '',
                $result->isSuccess() ? 'ok' : 'failed',
                $result->errorMessage(),
            ], ',', '"', '\\');
        }

        fclose($handle);
    }
}
