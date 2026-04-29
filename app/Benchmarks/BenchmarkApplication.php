<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks;

use RuntimeException;

/**
 * Framework-independent benchmark suite entrypoint.
 */
class BenchmarkApplication
{
    /**
     * Creates the benchmark application.
     */
    public function __construct(
        private readonly string $repositoryRoot,
        private readonly string $resultsDirectory,
        private readonly array $caseClasses,
    ) {
    }

    /**
     * Executes the benchmark suite and persists the run history.
     */
    public function run(BenchmarkOptions $options): BenchmarkRunReport
    {
        $this->ensureMemoryLimit();

        $runner = new BenchmarkRunner($this->benchmarkCases());

        $results = $runner->run($options->scale(), $options->iterations(), $options->filter());
        $historyWriter = new BenchmarkHistoryWriter($this->repositoryRoot, $this->resultsDirectory);
        $paths = $historyWriter->write($options->scale(), $options->iterations(), $results);

        return new BenchmarkRunReport(
            $options,
            $results,
            $paths['json_file'],
            $paths['csv_file'],
        );
    }

    /**
     * Raises the PHP memory limit for benchmark runs when possible.
     */
    private function ensureMemoryLimit(): void
    {
        @ini_set('memory_limit', '512M');
    }

    /**
     * Instantiates benchmark case objects from configuration.
     *
     * @return list<BenchmarkCaseInterface>
     */
    private function benchmarkCases(): array
    {
        $cases = [];

        foreach ($this->caseClasses as $caseClass) {
            if (!is_string($caseClass) || $caseClass === '') {
                throw new RuntimeException('Benchmark case configuration must contain class names.');
            }

            if (!class_exists($caseClass)) {
                throw new RuntimeException(sprintf('Configured benchmark case class not found: %s', $caseClass));
            }

            $case = new $caseClass();
            if (!$case instanceof BenchmarkCaseInterface) {
                throw new RuntimeException(sprintf('Configured benchmark case must implement %s: %s', BenchmarkCaseInterface::class, $caseClass));
            }

            $cases[] = $case;
        }

        return $cases;
    }
}
