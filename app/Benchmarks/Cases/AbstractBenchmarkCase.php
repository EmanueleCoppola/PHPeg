<?php

declare(strict_types=1);

namespace EmanueleCoppola\PHPeg\App\Benchmarks\Cases;

use EmanueleCoppola\PHPeg\App\Benchmarks\BenchmarkCaseInterface;
use EmanueleCoppola\PHPeg\Loader\CleanPeg\CleanPegGrammarLoader;
use EmanueleCoppola\PHPeg\Result\ParseResult;
use RuntimeException;

/**
 * Provides shared helpers for deterministic CleanPeg benchmarks.
 */
abstract class AbstractBenchmarkCase implements BenchmarkCaseInterface
{
    /**
     * @var array<string, \EmanueleCoppola\PHPeg\Grammar\Grammar>
     */
    private array $grammars = [];

    /**
     * Returns the CleanPeg source for the requested scale.
     */
    abstract protected function grammarSource(string $scale): string;

    /**
     * Returns the benchmark start rule.
     */
    abstract protected function startRule(): string;

    /**
     * Returns scale-specific configuration values.
     *
     * @param array{small:int,medium:int,large:int} $sizes
     */
    protected function sizeForScale(string $scale, array $sizes): int
    {
        if (!array_key_exists($scale, $sizes)) {
            throw new RuntimeException(sprintf('Unknown benchmark scale: %s', $scale));
        }

        return $sizes[$scale];
    }

    /**
     * Builds and caches the grammar for the requested scale.
     */
    public function grammar(string $scale): \EmanueleCoppola\PHPeg\Grammar\Grammar
    {
        if (!isset($this->grammars[$scale])) {
            $this->grammars[$scale] = (new CleanPegGrammarLoader(skipPattern: null))->fromString(
                $this->grammarSource($scale),
                $this->startRule(),
            );
        }

        return $this->grammars[$scale];
    }

    /**
     * Performs common parse validation for successful full-input matches.
     */
    protected function assertSuccessfulFullMatch(ParseResult $result, string $input): void
    {
        if (!$result->isSuccess()) {
            throw new RuntimeException($result->error()?->message() ?? 'Benchmark parse failed.');
        }

        if ($result->node() === null) {
            throw new RuntimeException('Benchmark parse did not return a root AST node.');
        }

        if ($result->node()->name() !== $this->startRule()) {
            throw new RuntimeException(sprintf('Expected root node "%s", got "%s".', $this->startRule(), $result->node()->name()));
        }

        if ($result->matchedText() !== $input) {
            throw new RuntimeException('Benchmark parse did not consume the full generated input.');
        }
    }
}
