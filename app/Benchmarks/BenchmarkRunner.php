<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

use RuntimeException;
use Throwable;

/**
 * Executes benchmark cases and collects timing and memory metrics.
 */
class BenchmarkRunner
{
    /**
     * @param list<BenchmarkCaseInterface> $cases
     */
    public function __construct(
        private readonly array $cases,
    ) {
    }

    /**
     * Runs the configured benchmarks.
     *
     * @return list<BenchmarkResult>
     */
    public function run(string $scale, int $iterations, ?string $filter = null): array
    {
        if ($iterations < 1) {
            throw new RuntimeException('Iterations must be greater than zero.');
        }

        $results = [];
        foreach ($this->filteredCases($filter) as $case) {
            $results[] = $this->runCase($case, $scale, $iterations);
        }

        if ($results === []) {
            throw new RuntimeException('No benchmarks matched the current filter.');
        }

        return $results;
    }

    /**
     * @return list<BenchmarkCaseInterface>
     */
    private function filteredCases(?string $filter): array
    {
        if ($filter === null || $filter === '') {
            return $this->cases;
        }

        $matches = [];
        foreach ($this->cases as $case) {
            if (stripos($case->name(), $filter) !== false || stripos($case->slug(), $filter) !== false) {
                $matches[] = $case;
            }
        }

        return $matches;
    }

    /**
     * Runs one benchmark case for the requested number of iterations.
     */
    private function runCase(BenchmarkCaseInterface $case, string $scale, int $iterations): BenchmarkResult
    {
        $grammar = $case->grammar($scale);
        $input = $case->input($scale);
        $inputSizeBytes = strlen($input);

        $times = [];
        $peaks = [];
        $memoryBefore = [];
        $memoryAfter = [];
        $memoryDelta = [];

        try {
            for ($iteration = 0; $iteration < $iterations; $iteration++) {
                gc_collect_cycles();

                if (function_exists('memory_reset_peak_usage')) {
                    memory_reset_peak_usage();
                }

                $before = memory_get_usage(true);
                $startedAt = hrtime(true);
                $result = $grammar->parse($input);
                $elapsedMs = (hrtime(true) - $startedAt) / 1_000_000;
                $case->validate($result, $input);
                $after = memory_get_usage(true);
                $peak = memory_get_peak_usage(true);

                $times[] = $elapsedMs;
                $peaks[] = $peak;
                $memoryBefore[] = $before;
                $memoryAfter[] = $after;
                $memoryDelta[] = $after - $before;

                unset($result);
                gc_collect_cycles();
            }
        } catch (Throwable $throwable) {
            return $this->failedResult($case, $iterations, $inputSizeBytes, $times, $peaks, $memoryBefore, $memoryAfter, $memoryDelta, $throwable);
        }

        return new BenchmarkResult(
            $case->name(),
            $case->slug(),
            $iterations,
            $inputSizeBytes,
            array_sum($times),
            $this->average($times),
            min($times),
            max($times),
            $peaks === [] ? 0 : max($peaks),
            $this->averageInt($memoryBefore),
            $this->averageInt($memoryAfter),
            $this->averageInt($memoryDelta),
            true,
            null,
        );
    }

    /**
     * Builds a failed benchmark result from partial measurements.
     *
     * @param list<float> $times
     * @param list<int> $peaks
     * @param list<int> $memoryBefore
     * @param list<int> $memoryAfter
     * @param list<int> $memoryDelta
     */
    private function failedResult(
        BenchmarkCaseInterface $case,
        int $iterations,
        int $inputSizeBytes,
        array $times,
        array $peaks,
        array $memoryBefore,
        array $memoryAfter,
        array $memoryDelta,
        Throwable $throwable,
    ): BenchmarkResult {
        return new BenchmarkResult(
            $case->name(),
            $case->slug(),
            $iterations,
            $inputSizeBytes,
            array_sum($times),
            $this->average($times),
            $times === [] ? 0.0 : min($times),
            $times === [] ? 0.0 : max($times),
            $peaks === [] ? 0 : max($peaks),
            $this->averageInt($memoryBefore),
            $this->averageInt($memoryAfter),
            $this->averageInt($memoryDelta),
            false,
            $throwable->getMessage(),
        );
    }

    /**
     * Returns the average value for a float list.
     *
     * @param list<float> $values
     */
    private function average(array $values): float
    {
        if ($values === []) {
            return 0.0;
        }

        return array_sum($values) / count($values);
    }

    /**
     * Returns the rounded average for an integer list.
     *
     * @param list<int> $values
     */
    private function averageInt(array $values): int
    {
        if ($values === []) {
            return 0;
        }

        return (int) round(array_sum($values) / count($values));
    }
}
