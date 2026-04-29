<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Commands;

use EmanueleCoppola\PHPeg\App\Benchmarks\BenchmarkComparisonRenderer;
use EmanueleCoppola\PHPeg\App\Benchmarks\BenchmarkComparisonService;
use LaravelZero\Framework\Commands\Command;

/**
 * Compares the latest two benchmark runs through Laravel Zero.
 */
class BenchmarkCompareCommand extends Command
{
    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'benchmark:compare';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Compare the latest two persisted benchmark runs.';

    /**
     * Executes the benchmark comparison command.
     */
    public function handle(): int
    {
        $service = new BenchmarkComparisonService((string) config('benchmarks.results_directory'));
        $report = $service->compareLatest();
        $renderer = new BenchmarkComparisonRenderer();

        $this->output->write($renderer->render($report) . PHP_EOL);

        return self::SUCCESS;
    }
}
