<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

/**
 * Renders comparison output from persisted benchmark history.
 */
class BenchmarkComparisonRenderer
{
    /**
     * Creates the comparison renderer.
     */
    public function __construct(
        private readonly BenchmarkConsoleRenderer $formatter = new BenchmarkConsoleRenderer(),
    ) {
    }

    /**
     * Renders the comparison report as human-readable text.
     */
    public function render(BenchmarkComparisonReport $report): string
    {
        $output = [
            'Comparing benchmark runs',
            'Previous: ' . $report->previousFile(),
            'Current: ' . $report->currentFile(),
            '',
        ];

        foreach ($report->rows() as $row) {
            $output[] = 'Benchmark: ' . $row->name();
            $output[] = 'Mode: ' . $row->mode();

            if ($row->previousAverageTimeMs() === null || $row->previousPeakMemoryBytes() === null) {
                $output[] = 'Previous run: unavailable';
                $output[] = '';
                continue;
            }

            $output[] = 'Previous avg time: ' . $this->formatter->formatMilliseconds($row->previousAverageTimeMs());
            $output[] = 'Current avg time: ' . $this->formatter->formatMilliseconds($row->currentAverageTimeMs());
            $output[] = 'Time change: ' . $this->formatPercentDifference($row->previousAverageTimeMs(), $row->currentAverageTimeMs());
            $output[] = 'Previous peak memory: ' . $this->formatter->formatBytes((int) $row->previousPeakMemoryBytes());
            $output[] = 'Current peak memory: ' . $this->formatter->formatBytes((int) $row->currentPeakMemoryBytes());
            $output[] = 'Memory change: ' . $this->formatPercentDifference($row->previousPeakMemoryBytes(), $row->currentPeakMemoryBytes());
            $output[] = '';
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * Formats a percentage difference between two numeric values.
     */
    public function formatPercentDifference(float $previous, float $current): string
    {
        if ($previous === 0.0) {
            return 'n/a';
        }

        $difference = (($current - $previous) / $previous) * 100;
        $sign = $difference > 0 ? '+' : '';

        return $sign . number_format($difference, 2) . '%';
    }
}
