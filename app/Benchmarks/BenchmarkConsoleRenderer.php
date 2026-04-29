<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

/**
 * Renders benchmark output for human and machine consumers.
 */
class BenchmarkConsoleRenderer
{
    /**
     * Renders the benchmark report as human-readable text.
     */
    public function renderHuman(BenchmarkRunReport $report): string
    {
        $output = [];

        foreach ($report->results() as $result) {
            $output[] = 'Benchmark: ' . $result->name();
            $output[] = 'Input size: ' . $this->formatBytes($result->inputSizeBytes());
            $output[] = 'Iterations: ' . $result->iterations();
            $output[] = 'Total time: ' . $this->formatMilliseconds($result->totalTimeMs());
            $output[] = 'Avg time: ' . $this->formatMilliseconds($result->averageTimeMs());
            $output[] = 'Min time: ' . $this->formatMilliseconds($result->minTimeMs());
            $output[] = 'Max time: ' . $this->formatMilliseconds($result->maxTimeMs());
            $output[] = 'Peak memory: ' . $this->formatBytes($result->peakMemoryBytes());
            $output[] = 'Memory before: ' . $this->formatBytes($result->memoryBeforeBytes());
            $output[] = 'Memory after: ' . $this->formatBytes($result->memoryAfterBytes());
            $output[] = 'Memory delta: ' . $this->formatBytes($result->memoryDeltaBytes(), true);
            $output[] = 'Result: ' . ($result->isSuccess() ? 'OK' : 'FAILED');

            if (!$result->isSuccess() && $result->errorMessage() !== null) {
                $output[] = 'Error: ' . $result->errorMessage();
            }

            $output[] = '';
        }

        $output[] = 'Saved JSON: ' . $report->jsonFile();
        $output[] = 'Saved CSV: ' . $report->csvFile();

        return implode(PHP_EOL, $output);
    }

    /**
     * Renders the benchmark report as JSON.
     */
    public function renderJson(BenchmarkRunReport $report): string
    {
        $json = json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $json === false ? '{}' : $json;
    }

    /**
     * Formats milliseconds with fixed precision.
     */
    public function formatMilliseconds(float $milliseconds): string
    {
        return number_format($milliseconds, 2) . ' ms';
    }

    /**
     * Formats bytes into human-readable units.
     */
    public function formatBytes(int $bytes, bool $signed = false): string
    {
        $sign = '';
        $value = $bytes;
        if ($signed && $bytes > 0) {
            $sign = '+';
        }

        if ($bytes < 0) {
            $sign = '-';
            $value = abs($bytes);
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        $formatted = (float) $value;

        while ($formatted >= 1024 && $unitIndex < count($units) - 1) {
            $formatted /= 1024;
            $unitIndex++;
        }

        return sprintf('%s%.2f %s', $sign, $formatted, $units[$unitIndex]);
    }
}
